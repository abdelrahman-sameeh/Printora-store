<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderItemPicture;
use App\Models\Order\SubOrder;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // ─── Customer ─────────────────────────────────────────────────────────────

    /**
     * عرض كل الأوردرات بتاعة اليوزر.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['subOrders.items.pictures'])
            ->latest()
            ->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * عرض أوردر واحد.
     */
    public function show(Request $request, Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403, 'Forbidden.');

        $order->load(['address', 'subOrders.seller:id,first_name,last_name,email', 'subOrders.items.pictures']);

        return response()->json(['order' => $order]);
    }

    /**
     * إنشاء أوردر جديد من الـ cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:15',
            'address_id' => 'nullable|exists:addresses,id',
            'payment_method' => 'required|in:cash,card,wallet',
        ]);

        $user = $request->user();
        $cart = $user->cart;

        abort_if(! $cart || $cart->items()->count() === 0, 400, 'Cart is empty.');

        // تحميل كل الـ items مع المنتجات
        $cart->load('items.product', 'coupons.coupon');

        // التحقق من الـ stock
        foreach ($cart->items as $item) {
            $product = $item->product;
            abort_if(! $product || ! $product->is_active, 422, "Product '{$item->product_id}' is unavailable.");
            abort_if($product->quantity < $item->quantity, 422, "Insufficient stock for '{$product->title}'.");
        }

        DB::transaction(function () use ($user, $cart, $validated) {

            // تجميع الـ items حسب الـ seller
            $groups = [];
            foreach ($cart->items as $item) {
                $sellerId = $item->product->seller_id;
                $groups[$sellerId][] = $item;
            }

            // حساب الـ totals لكل seller مع مراعاة الكوبون
            $couponsBySeller = [];
            foreach ($cart->coupons as $cartCoupon) {
                $coupon = $cartCoupon->coupon;
                if ($coupon && ! $coupon->is_invalid()) {
                    $couponsBySeller[$coupon->seller_id] = $coupon;
                }
            }

            $cartSubtotal = 0;
            $cartDiscount = 0;
            $subOrdersData = [];

            foreach ($groups as $sellerId => $items) {
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += $item->product->price * $item->quantity;
                }

                $discount = 0;
                if (isset($couponsBySeller[$sellerId])) {
                    $coupon = $couponsBySeller[$sellerId];
                    $discount = round($subtotal * ($coupon->percentage / 100), 2);
                }

                $subOrdersData[$sellerId] = [
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $subtotal - $discount,
                ];

                $cartSubtotal += $subtotal;
                $cartDiscount += $discount;
            }

            // إنشاء الـ Order الرئيسي
            $order = Order::create([
                'user_id' => $user->id,
                'subtotal' => $cartSubtotal,
                'discount' => $cartDiscount,
                'total_price' => $cartSubtotal - $cartDiscount,
                'phone' => $validated['phone'],
                'address_id' => $validated['address_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
            ]);

            // إنشاء SubOrder لكل seller
            foreach ($subOrdersData as $sellerId => $data) {
                $subOrder = SubOrder::create([
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'subtotal' => $data['subtotal'],
                    'discount' => $data['discount'],
                    'total_price' => $data['total'],
                    'status' => 'pending',
                ]);

                // إنشاء OrderItems كـ snapshot
                foreach ($data['items'] as $item) {
                    $product = $item->product;
                    $orderItem = OrderItem::create([
                        'sub_order_id' => $subOrder->id,
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'cover_image' => $product->cover_image,
                        'price_at_purchase' => $product->price,
                        'quantity' => $item->quantity,
                        'created_at_snapshot' => $product->created_at,
                    ]);

                    // نسخ الصور
                    foreach ($product->pictures as $pic) {
                        OrderItemPicture::create([
                            'order_item_id' => $orderItem->id,
                            'image_path' => $pic->picture,
                        ]);
                    }

                    // نقص الـ stock
                    $product->decrement('quantity', $item->quantity);
                }
            }

            // تفريغ الـ cart
            $cart->items()->delete();
            DB::table('cart_coupons')->where('cart_id', $cart->id)->delete();
        });

        return response()->json(['message' => 'Order placed successfully.'], 201);
    }

    // ─── Seller ───────────────────────────────────────────────────────────────

    /**
     * عرض الـ sub_orders بتاعة الـ seller.
     */
    public function sellerOrders(Request $request)
    {
        $subOrders = SubOrder::where('seller_id', $request->user()->id)
            ->with(['order:id,user_id,phone,address_id,payment_method,payment_status', 'items.pictures'])
            ->latest()
            ->get();

        return response()->json(['sub_orders' => $subOrders]);
    }

    /**
     * الـ seller يغير حالة الـ sub_order بتاعه.
     */
    public function updateSubOrderStatus(Request $request, SubOrder $subOrder)
    {
        abort_if($subOrder->seller_id !== $request->user()->id, 403, 'Forbidden.');

        $validated = $request->validate([
            'status' => 'required|in:processing,shipped,completed,cancelled',
        ]);

        $subOrder->update(['status' => $validated['status']]);

        // تحديث حالة الـ Order الرئيسي تلقائياً
        $this->syncOrderStatus($subOrder->order);

        return response()->json(['message' => 'Status updated.', 'sub_order' => $subOrder->fresh()]);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    /**
     * مزامنة حالة الـ Order بناءً على حالات الـ SubOrders.
     */
    private function syncOrderStatus(Order $order): void
    {
        $statuses = $order->subOrders()->pluck('status')->toArray();

        if (in_array('cancelled', $statuses) && count(array_unique($statuses)) === 1) {
            $order->update(['status' => 'cancelled']);
        } elseif (in_array('pending', $statuses) || in_array('processing', $statuses)) {
            $order->update(['status' => 'processing']);
        } elseif (in_array('shipped', $statuses)) {
            $order->update(['status' => 'shipped']);
        } elseif (count(array_unique($statuses)) === 1 && $statuses[0] === 'completed') {
            $order->update(['status' => 'completed']);
        }
    }
}
