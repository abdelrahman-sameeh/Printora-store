<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Cart\Cart;
use App\Models\Cart\CartCoupon;
use App\Models\Cart\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Order\SubOrder;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductSubCategory;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Users: one admin, one seller, and two buyers.
        $admin = $this->createUser(
            'Admin',
            'System',
            'admin@gmail.com',
            RoleName::ADMIN,
        );

        $seller = $this->createUser(
            'Printora',
            'Fashion',
            'printora.shop0@gmail.com',
            RoleName::SELLER,
        );

        $buyer1 = $this->createUser(
            'Ali',
            'Customer',
            'buyer1@gmail.com',
            RoleName::USER,
        );

        $buyer2 = $this->createUser(
            'Sara',
            'Customer',
            'buyer2@gmail.com',
            RoleName::USER,
        );

        // Addresses.
        $buyer1Address = Address::create([
            'user_id' => $buyer1->id,
            'country' => 'EG',
            'city' => 'Cairo',
            'street' => '15 Tahrir Square, Downtown',
            'is_default' => true,
            'note' => 'Next to the metro station',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        Address::create([
            'user_id' => $buyer1->id,
            'country' => 'EG',
            'city' => 'Giza',
            'street' => '22 Pyramids Road',
            'is_default' => false,
            'note' => 'Near the mall',
        ]);

        $buyer2Address = Address::create([
            'user_id' => $buyer2->id,
            'country' => 'EG',
            'city' => 'Alexandria',
            'street' => '5 Corniche Road',
            'is_default' => true,
        ]);

        // Clothing categories.
        $men = Category::create(['title' => 'Men Clothing']);
        $women = Category::create(['title' => 'Women Clothing']);
        $kids = Category::create(['title' => 'Kids Clothing']);

        $menTShirts = SubCategory::create([
            'title' => 'Men T-Shirts',
            'category_id' => $men->id,
        ]);
        SubCategory::create([
            'title' => 'Men Casual Wear',
            'category_id' => $men->id,
        ]);
        $womenTShirts = SubCategory::create([
            'title' => 'Women T-Shirts',
            'category_id' => $women->id,
        ]);
        SubCategory::create([
            'title' => 'Women Casual Wear',
            'category_id' => $women->id,
        ]);
        SubCategory::create([
            'title' => 'Kids T-Shirts',
            'category_id' => $kids->id,
        ]);
        $kidsSportswear = SubCategory::create([
            'title' => 'Kids Sportswear',
            'category_id' => $kids->id,
        ]);

        // Every product is clothing and uses an existing public/storage image.
        $products = [
            Product::create([
                'title' => 'Classic Burgundy T-Shirt',
                'description' => 'A soft cotton T-shirt with a comfortable regular fit for everyday wear.',
                'cover_image' => '/storage/products/covers/3fhjagZjKUaDlyIBtfhIb1NfwmXQjQC5lTNfUbHM.webp',
                'price' => 499.00,
                'discount_amount' => 50.00,
                'quantity' => 80,
                'seller_id' => $seller->id,
                'is_active' => true,
            ]),
            Product::create([
                'title' => 'Essential Stripe T-Shirt',
                'description' => 'A lightweight striped-sleeve T-shirt made for a clean casual look.',
                'cover_image' => '/storage/products/covers/UsvsVsvtfi5ludnao1MHZn0eUvOMoFUUErH1gwGN.webp',
                'price' => 549.00,
                'discount_amount' => 0,
                'quantity' => 65,
                'seller_id' => $seller->id,
                'is_active' => true,
            ]),
            Product::create([
                'title' => 'Women Casual Cotton T-Shirt',
                'description' => 'A breathable cotton T-shirt with a relaxed fit for daily outfits.',
                'cover_image' => '/storage/products/covers/dGx7GHVweNGFbWZZDjCd1kyeVcNntASfIhNzumk8.webp',
                'price' => 599.00,
                'discount_amount' => 75.00,
                'quantity' => 55,
                'seller_id' => $seller->id,
                'is_active' => true,
            ]),
            Product::create([
                'title' => 'Kids Burgundy Sports T-Shirt',
                'description' => 'A durable sports T-shirt for kids with soft fabric and easy movement.',
                'cover_image' => '/storage/products/gallery/U8nNE05WagOePq1D0S0xBwDKiR4VMRqL3blk95nR.webp',
                'price' => 399.00,
                'discount_amount' => 25.00,
                'quantity' => 90,
                'seller_id' => $seller->id,
                'is_active' => true,
            ]),
        ];

        $subCategories = [
            $menTShirts,
            $menTShirts,
            $womenTShirts,
            $kidsSportswear,
        ];

        foreach ($products as $index => $product) {
            ProductSubCategory::create([
                'product_id' => $product->id,
                'sub_category_id' => $subCategories[$index]->id,
            ]);
        }

        $attributes = [
            [['Color', 'Burgundy'], ['Size', 'M, L, XL'], ['Material', '100% Cotton']],
            [['Color', 'Burgundy'], ['Size', 'S, M, L'], ['Fit', 'Regular']],
            [['Color', 'Burgundy'], ['Size', 'S, M, L'], ['Material', 'Cotton']],
            [['Color', 'Burgundy'], ['Size', '6-12 Years'], ['Fit', 'Sports']],
        ];

        foreach ($products as $index => $product) {
            foreach ($attributes[$index] as [$key, $value]) {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }

        // Seller coupons.
        $fashionCoupon = Coupon::create([
            'code' => 'FASHION10',
            'percentage' => 10,
            'expire_date' => now()->addMonths(3)->toDateString(),
            'max_usage' => 100,
            'used_count' => 5,
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'EXPIRED25',
            'percentage' => 25,
            'expire_date' => now()->subDays(10)->toDateString(),
            'max_usage' => 10,
            'used_count' => 10,
            'seller_id' => $seller->id,
            'is_active' => false,
        ]);

        // Buyer carts.
        $buyer1Cart = Cart::create(['user_id' => $buyer1->id]);
        CartItem::create(['cart_id' => $buyer1Cart->id, 'product_id' => $products[0]->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $buyer1Cart->id, 'product_id' => $products[1]->id, 'quantity' => 2]);
        CartCoupon::create(['cart_id' => $buyer1Cart->id, 'coupon_id' => $fashionCoupon->id]);

        $buyer2Cart = Cart::create(['user_id' => $buyer2->id]);
        CartItem::create(['cart_id' => $buyer2Cart->id, 'product_id' => $products[2]->id, 'quantity' => 1]);
        CartItem::create(['cart_id' => $buyer2Cart->id, 'product_id' => $products[3]->id, 'quantity' => 1]);

        // Orders use clothing product snapshots only.
        $this->createOrder(
            buyer: $buyer1,
            seller: $seller,
            address: $buyer1Address,
            products: [$products[0], $products[1]],
            status: 'processing',
            paymentStatus: 'paid',
            paymentMethod: 'card',
            phone: '01012345678',
        );

        $this->createOrder(
            buyer: $buyer2,
            seller: $seller,
            address: $buyer2Address,
            products: [$products[2], $products[3]],
            status: 'completed',
            paymentStatus: 'paid',
            paymentMethod: 'cash',
            phone: '01198765432',
        );

        $this->showSummary();
    }

    private function createUser(
        string $firstName,
        string $lastName,
        string $email,
        RoleName $role,
    ): User {
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make('printora1234'),
        ]);

        $user->syncRoles($role);

        return $user;
    }

    /**
     * @param  array<int, Product>  $products
     */
    private function createOrder(
        User $buyer,
        User $seller,
        Address $address,
        array $products,
        string $status,
        string $paymentStatus,
        string $paymentMethod,
        string $phone,
    ): void {
        $subtotal = collect($products)->sum(fn (Product $product): float => (float) $product->price);

        $order = Order::create([
            'user_id' => $buyer->id,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total_price' => $subtotal,
            'phone' => $phone,
            'address_id' => $address->id,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
        ]);

        $subOrder = SubOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total_price' => $subtotal,
            'status' => $status,
        ]);

        foreach ($products as $product) {
            OrderItem::create([
                'sub_order_id' => $subOrder->id,
                'product_id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'cover_image' => $product->cover_image,
                'price_at_purchase' => $product->price,
                'quantity' => 1,
                'created_at_snapshot' => $product->created_at,
            ]);
        }
    }

    private function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('All test data seeded successfully.');
        $this->command->newLine();

        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['admin', 'admin@gmail.com', 'printora1234'],
                ['seller', 'seller@gmail.com', 'printora1234'],
                ['buyer', 'buyer1@gmail.com', 'printora1234'],
                ['buyer', 'buyer2@gmail.com', 'printora1234'],
            ],
        );

        $this->command->newLine();
        $this->command->table(
            ['Data', 'Count'],
            [
                ['Users', 4],
                ['Addresses', 3],
                ['Categories', 3],
                ['SubCategories', 6],
                ['Products', 4],
                ['Attributes', 12],
                ['Coupons', 2],
                ['Carts', 2],
                ['Cart Items', 4],
                ['Orders', 2],
                ['Sub Orders', 2],
                ['Order Items', 4],
            ],
        );
    }
}
