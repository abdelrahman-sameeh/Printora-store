<?php

namespace App\Services;

use App\Models\Cart\Cart;
use App\Models\Cart\CartCoupon;
use App\Models\Cart\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function get(User $user): ?Cart
    {
        return $user->cart()->first();
    }

    public function addItems(User $user, array $items): Cart
    {
        $cart = $user->cart()->firstOrCreate();

        foreach ($items as $itemData) {
            $product = Product::query()->findOrFail($itemData['id']);
            $item = $cart->items()->where('product_id', $product->id)->first();
            $quantity = ($item?->quantity ?? 0) + $itemData['quantity'];

            $this->ensureAvailable($product, $quantity);

            if ($item) {
                $item->update(['quantity' => $quantity]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ]);
            }
        }

        return $cart;
    }

    public function updateItem(User $user, int $itemId, int $quantity): CartItem
    {
        $item = $this->requireCart($user)->items()->with('product')->findOrFail($itemId);
        $this->ensureAvailable($item->product, $quantity);
        $item->update(['quantity' => $quantity]);

        return $item->refresh();
    }

    public function removeItem(User $user, int $itemId): void
    {
        $this->requireCart($user)->items()->findOrFail($itemId)->delete();
    }

    public function clear(User $user): void
    {
        $user->cart()->delete();
    }

    public function applyCoupon(User $user, Coupon $coupon): void
    {
        $cart = $this->requireCart($user);

        if ($coupon->is_invalid()) {
            throw ValidationException::withMessages(['coupon' => 'هذا الكوبون غير صالح للاستخدام.']);
        }

        if ($cart->coupons()->where('coupon_id', $coupon->id)->exists()) {
            throw ValidationException::withMessages(['coupon' => 'تم تطبيق هذا الكوبون بالفعل.']);
        }

        $hasCouponForSeller = $cart->coupons()
            ->whereHas('coupon', fn ($query) => $query->where('seller_id', $coupon->seller_id))
            ->exists();

        if ($hasCouponForSeller) {
            throw ValidationException::withMessages(['coupon' => 'يمكن تطبيق كوبون واحد فقط لكل بائع.']);
        }

        $hasEligibleProduct = $cart->items()
            ->whereHas('product', fn ($query) => $query
                ->where('seller_id', $coupon->seller_id)
                ->where('is_active', true))
            ->exists();

        if (! $hasEligibleProduct) {
            throw ValidationException::withMessages(['coupon' => 'لا تحتوي السلة على منتجات يقبلها هذا الكوبون.']);
        }

        CartCoupon::create([
            'cart_id' => $cart->id,
            'coupon_id' => $coupon->id,
        ]);
    }

    public function removeCoupon(User $user, int $couponId): void
    {
        $this->requireCart($user)
            ->coupons()
            ->where('coupon_id', $couponId)
            ->firstOrFail()
            ->delete();
    }

    public function validate(User $user): array
    {
        $cart = $this->get($user);

        if (! $cart || $cart->items()->doesntExist()) {
            return [
                'valid' => false,
                'errors_count' => 1,
                'errors' => [[
                    'type' => 'empty_cart',
                    'message' => 'السلة فارغة.',
                ]],
            ];
        }

        $cart->load(['items.product', 'coupons.coupon']);
        $errors = [];

        foreach ($cart->items as $item) {
            if (! $item->product->is_active) {
                $errors[] = [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'type' => 'product_inactive',
                    'message' => 'المنتج لم يعد متاحًا.',
                ];
            }

            if ($item->product->quantity < $item->quantity) {
                $errors[] = [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'type' => 'insufficient_stock',
                    'message' => "المتاح من المنتج {$item->product->quantity} فقط.",
                    'requested_quantity' => $item->quantity,
                    'available_quantity' => $item->product->quantity,
                ];
            }
        }

        foreach ($cart->coupons as $cartCoupon) {
            if ($cartCoupon->coupon->is_invalid()) {
                $errors[] = [
                    'coupon_id' => $cartCoupon->coupon_id,
                    'type' => 'invalid_coupon',
                    'message' => 'أحد كوبونات السلة لم يعد صالحًا.',
                ];
            }
        }

        return [
            'valid' => $errors === [],
            'errors_count' => count($errors),
            'errors' => $errors,
            ...($errors === [] ? ['cart' => $this->data($cart)] : []),
        ];
    }

    public function data(?Cart $cart): array
    {
        if (! $cart) {
            return $this->emptyData();
        }

        $cart->load(['items.product', 'coupons.coupon']);
        $groups = [];

        foreach ($cart->items as $item) {
            $sellerId = $item->product->seller_id;
            $groups[$sellerId] ??= [
                'seller_id' => $sellerId,
                'items' => [],
                'coupon' => null,
            ];
            $groups[$sellerId]['items'][] = [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'product' => [
                    'id' => $item->product->id,
                    'title' => $item->product->title,
                    'slug' => $item->product->slug,
                    'price' => (float) $item->product->price,
                    'cover_image' => $item->product->cover_image
                        ? $item->product->cover_image_url
                        : null,
                    'stock' => $item->product->quantity,
                ],
            ];
        }

        foreach ($cart->coupons as $cartCoupon) {
            $sellerId = $cartCoupon->coupon->seller_id;

            if (! isset($groups[$sellerId])) {
                continue;
            }

            $groups[$sellerId]['coupon'] = [
                'id' => $cartCoupon->coupon->id,
                'code' => $cartCoupon->coupon->code,
                'percentage' => $cartCoupon->coupon->percentage,
                'expire_date' => $cartCoupon->coupon->expire_date->format('Y-m-d'),
                'is_valid' => ! $cartCoupon->coupon->is_invalid(),
            ];
        }

        $subtotal = 0;
        $discount = 0;

        foreach ($groups as &$group) {
            $groupSubtotal = collect($group['items'])->sum(
                fn (array $item): float => $item['quantity'] * $item['product']['price']
            );
            $groupDiscount = $group['coupon'] && $group['coupon']['is_valid']
                ? $groupSubtotal * ($group['coupon']['percentage'] / 100)
                : 0;

            $group['summary'] = [
                'sub_total' => $groupSubtotal,
                'discount' => $groupDiscount,
                'total' => $groupSubtotal - $groupDiscount,
            ];
            $subtotal += $groupSubtotal;
            $discount += $groupDiscount;
        }
        unset($group);

        return [
            'id' => $cart->id,
            'items_count' => $cart->items->count(),
            'groups' => array_values($groups),
            'summary_cart' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $subtotal - $discount,
            ],
        ];
    }

    private function requireCart(User $user): Cart
    {
        return $user->cart()->firstOrFail();
    }

    private function ensureAvailable(Product $product, int $quantity): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages(['quantity' => 'هذا المنتج غير متاح حاليًا.']);
        }

        if ($quantity > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => "الكمية المتاحة من {$product->title} هي {$product->quantity} فقط.",
            ]);
        }
    }

    private function emptyData(): array
    {
        return [
            'id' => null,
            'items_count' => 0,
            'groups' => [],
            'summary_cart' => [
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0,
            ],
        ];
    }
}
