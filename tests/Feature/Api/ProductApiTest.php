<?php

namespace Tests\Feature\Api;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_is_stored_only_on_product_variants(): void
    {
        $this->assertFalse(Schema::hasColumn('products', 'quantity'));
        $this->assertTrue(Schema::hasColumn('product_variants', 'stock'));
        $this->assertFalse(Schema::hasColumn('product_variants', 'quantity'));
    }

    public function test_seller_can_create_and_update_product_variants_through_the_api(): void
    {
        Storage::fake('public');
        $seller = User::create([
            'first_name' => 'بائع',
            'last_name' => 'تجريبي',
            'email' => 'variant-seller@example.com',
            'password' => 'password123',
        ])->syncRoles(RoleName::SELLER);
        $category = Category::create(['title' => 'ملابس']);
        $subCategory = SubCategory::create([
            'title' => 'تيشيرتات',
            'category_id' => $category->id,
        ]);
        Sanctum::actingAs($seller);

        $response = $this->post('/api/products', [
            'title' => 'تيشيرت متعدد المقاسات',
            'description' => 'تيشيرت قطني متاح بأكثر من مقاس ولون.',
            'price' => 300,
            'discount_amount' => 25,
            'cover_image' => $this->fakeImage(),
            'sub_categories' => [$subCategory->id],
            'variants' => [
                ['size' => 'M', 'color' => 'أسود', 'stock' => 5],
                ['size' => 'L', 'color' => 'أبيض', 'stock' => 3],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.stock', 8)
            ->assertJsonFragment(['size' => 'M', 'color' => 'أسود', 'stock' => 5])
            ->assertJsonFragment(['size' => 'L', 'color' => 'أبيض', 'stock' => 3]);
        $productId = $response->json('data.id');

        $this->patchJson("/api/products/{$productId}", [
            'variants' => [
                ['size' => 'XL', 'color' => 'كحلي', 'stock' => 4],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('stock', 4)
            ->assertJsonCount(1, 'variants')
            ->assertJsonPath('variants.0.size', 'XL')
            ->assertJsonPath('variants.0.color', 'كحلي');

        $this->assertDatabaseMissing('product_variants', [
            'product_id' => $productId,
            'size' => 'M',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $productId,
            'size' => 'XL',
            'color' => 'كحلي',
            'stock' => 4,
        ]);
    }

    public function test_duplicate_size_and_color_combination_is_rejected(): void
    {
        $seller = User::create([
            'first_name' => 'بائع',
            'last_name' => 'تجريبي',
            'email' => 'duplicate-variant@example.com',
            'password' => 'password123',
        ])->syncRoles(RoleName::SELLER);
        $category = Category::create(['title' => 'ملابس مكررة']);
        $subCategory = SubCategory::create([
            'title' => 'قمصان',
            'category_id' => $category->id,
        ]);
        Sanctum::actingAs($seller);

        $this->post('/api/products', [
            'title' => 'منتج بتكرار',
            'description' => 'وصف صالح لاختبار منع تكرار الاختيارات.',
            'price' => 100,
            'cover_image' => $this->fakeImage(),
            'sub_categories' => [$subCategory->id],
            'variants' => [
                ['size' => 'M', 'color' => 'أسود', 'stock' => 2],
                ['size' => 'm', 'color' => ' أسود ', 'stock' => 3],
            ],
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['variants.1.color']);
    }

    private function fakeImage(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'shirt.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );
    }
}
