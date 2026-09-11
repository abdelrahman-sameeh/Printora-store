<?php

namespace Tests\Feature\Web;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_search_form_opens_the_dedicated_search_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('action="'.route('products.search').'"', false);
    }

    public function test_search_page_searches_active_products(): void
    {
        $seller = $this->createSeller();
        $matchingProduct = $seller->products()->create([
            'title' => 'Wireless Headphones',
            'description' => 'Comfortable audio product.',
            'cover_image' => '/storage/products/covers/headphones.png',
            'price' => 800,
            'discount_amount' => 50,
            'quantity' => 5,
        ]);
        $seller->products()->create([
            'title' => 'Coffee Maker',
            'description' => 'Makes fresh coffee.',
            'cover_image' => '/storage/products/covers/coffee.png',
            'price' => 500,
            'discount_amount' => 0,
            'quantity' => 2,
        ]);
        $seller->products()->create([
            'title' => 'Wireless Mouse',
            'description' => 'This inactive product must stay hidden.',
            'cover_image' => '/storage/products/covers/mouse.png',
            'price' => 300,
            'discount_amount' => 0,
            'quantity' => 4,
            'is_active' => false,
        ]);

        $this->get(route('products.search', ['q' => 'Wireless']))
            ->assertOk()
            ->assertSee('Wireless Headphones')
            ->assertSee(route('products.show', $matchingProduct), false)
            ->assertSee('stretched-link', false)
            ->assertDontSee('Coffee Maker')
            ->assertDontSee('Wireless Mouse');
    }

    public function test_search_page_can_search_by_attribute_and_category(): void
    {
        $seller = $this->createSeller();
        $category = Category::create(['title' => 'Electronics']);
        $subCategory = SubCategory::create([
            'title' => 'Phones',
            'category_id' => $category->id,
        ]);
        $product = $seller->products()->create([
            'title' => 'Creator Device',
            'description' => 'A device for professionals.',
            'cover_image' => '/storage/products/covers/device.png',
            'price' => 1200,
            'discount_amount' => 0,
            'quantity' => 3,
        ]);
        $product->attributes()->create(['key' => 'color', 'value' => 'Midnight Black']);
        $product->sub_categories()->attach($subCategory);

        $this->get(route('products.search', ['q' => 'Midnight']))
            ->assertOk()
            ->assertSee('Creator Device');

        $this->get(route('products.search', ['q' => 'Phones']))
            ->assertOk()
            ->assertSee('Creator Device');
    }

    public function test_search_page_combines_filters_and_sorts_by_discounted_price(): void
    {
        $seller = $this->createSeller();
        $category = Category::create(['title' => 'Fashion']);
        $subCategory = SubCategory::create([
            'title' => 'Shoes',
            'category_id' => $category->id,
        ]);
        $otherCategory = Category::create(['title' => 'Home']);
        $otherSubCategory = SubCategory::create([
            'title' => 'Kitchen',
            'category_id' => $otherCategory->id,
        ]);

        $matchingProduct = $seller->products()->create([
            'title' => 'Premium Shoes',
            'description' => 'Discounted shoes available in stock.',
            'cover_image' => '/storage/products/covers/premium-shoes.png',
            'price' => 500,
            'discount_amount' => 200,
            'quantity' => 4,
        ]);
        $matchingProduct->sub_categories()->attach($subCategory);

        $cheapProduct = $seller->products()->create([
            'title' => 'Basic Shoes',
            'description' => 'Shoes below the selected price range.',
            'cover_image' => '/storage/products/covers/basic-shoes.png',
            'price' => 150,
            'discount_amount' => 20,
            'quantity' => 5,
        ]);
        $cheapProduct->sub_categories()->attach($subCategory);

        $outOfStockProduct = $seller->products()->create([
            'title' => 'Limited Shoes',
            'description' => 'Shoes unavailable in stock.',
            'cover_image' => '/storage/products/covers/limited-shoes.png',
            'price' => 320,
            'discount_amount' => 20,
            'quantity' => 0,
        ]);
        $outOfStockProduct->sub_categories()->attach($subCategory);

        $otherProduct = $seller->products()->create([
            'title' => 'Kitchen Set',
            'description' => 'A product in another category.',
            'cover_image' => '/storage/products/covers/kitchen.png',
            'price' => 300,
            'discount_amount' => 50,
            'quantity' => 2,
        ]);
        $otherProduct->sub_categories()->attach($otherSubCategory);

        $this->get(route('products.search', ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('Premium Shoes');

        $this->get(route('products.search', ['min_price' => 250, 'max_price' => 350]))
            ->assertOk()
            ->assertSee('Premium Shoes');

        $this->get(route('products.search', ['max_price' => 150]))
            ->assertOk()
            ->assertSee('Basic Shoes')
            ->assertDontSee('Premium Shoes');

        $this->get(route('products.search', ['in_stock' => 1, 'on_sale' => 1]))
            ->assertOk()
            ->assertSee('Premium Shoes');

        $this->get(route('products.search', [
            'category_id' => $category->id,
            'min_price' => 250,
            'max_price' => 350,
            'in_stock' => 1,
            'on_sale' => 1,
        ]))
            ->assertOk()
            ->assertSee('Premium Shoes')
            ->assertDontSee('Basic Shoes')
            ->assertDontSee('Limited Shoes')
            ->assertDontSee('Kitchen Set');

        $this->get(route('products.search', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Premium Shoes', 'Limited Shoes', 'Kitchen Set', 'Basic Shoes']);

        $this->get(route('products.search', ['min_price' => 500, 'max_price' => 100]))
            ->assertRedirect(route('products.search'))
            ->assertSessionHasErrors('max_price');
    }

    public function test_guest_can_view_an_active_product_but_not_an_inactive_one(): void
    {
        $seller = $this->createSeller();
        $activeProduct = $seller->products()->create([
            'title' => 'Public Product',
            'description' => 'This product is visible to storefront visitors.',
            'cover_image' => '/storage/products/covers/public.png',
            'price' => 250,
            'discount_amount' => 25,
            'quantity' => 7,
        ]);
        $activeProduct->attributes()->create(['key' => 'size', 'value' => 'Medium']);
        $activeProduct->variants()->create([
            'size' => 'M',
            'color' => 'أسود',
            'quantity' => 7,
        ]);
        $inactiveProduct = $seller->products()->create([
            'title' => 'Hidden Product',
            'description' => 'This product is not publicly available.',
            'cover_image' => '/storage/products/covers/hidden.png',
            'price' => 100,
            'discount_amount' => 0,
            'quantity' => 1,
            'is_active' => false,
        ]);

        $this->get(route('products.show', $activeProduct))
            ->assertOk()
            ->assertSee('Public Product')
            ->assertSee('225.00')
            ->assertSee('Medium')
            ->assertSee('M')
            ->assertSee('أسود');

        $this->get(route('products.show', $inactiveProduct))
            ->assertNotFound();
    }

    public function test_buyer_selects_color_and_size_separately_before_adding_to_cart(): void
    {
        $seller = $this->createSeller();
        $buyer = User::create([
            'first_name' => 'مشتري',
            'last_name' => 'تجريبي',
            'email' => 'variant-buyer@example.com',
            'password' => 'password123',
        ]);
        $buyer->syncRoles(RoleName::USER);
        $product = $seller->products()->create([
            'title' => 'قميص ملون',
            'description' => 'قميص متاح بألوان ومقاسات مختلفة.',
            'price' => 250,
            'discount_amount' => 0,
            'quantity' => 5,
        ]);
        $product->variants()->createMany([
            ['size' => 'M', 'color' => 'أسود', 'quantity' => 3],
            ['size' => 'L', 'color' => 'أزرق', 'quantity' => 2],
        ]);

        $this->actingAs($buyer)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('id="variant-color"', false)
            ->assertSee('id="variant-size"', false)
            ->assertSee('name="product_variant_id"', false)
            ->assertSee('اختار المناسب ليك')
            ->assertSee('id="variant-summary"', false)
            ->assertSee('data-action="increase"', false)
            ->assertSee('أسود')
            ->assertSee('أزرق');
    }

    private function createSeller(): User
    {
        $seller = User::create([
            'first_name' => 'Storefront',
            'last_name' => 'Seller',
            'email' => 'storefront-seller@example.com',
            'password' => 'password123',
        ]);

        return $seller->syncRoles(RoleName::SELLER);
    }
}
