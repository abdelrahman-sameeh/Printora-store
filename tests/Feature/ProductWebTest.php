<?php

namespace Tests\Feature;

use App\Constants\UserRole;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductWebTest extends TestCase
{
  use RefreshDatabase;

  public function test_only_sellers_can_open_product_management(): void
  {
    $user = User::create([
      'first_name' => 'Buyer',
      'last_name' => 'User',
      'email' => 'buyer@example.com',
      'password' => 'password123',
      'role' => UserRole::USER,
    ]);

    $this->actingAs($user)
      ->get(route('seller.products.index'))
      ->assertForbidden();
  }

  public function test_seller_can_create_a_product_from_web(): void
  {
    Storage::fake('public');

    $seller = User::create([
      'first_name' => 'Seller',
      'last_name' => 'User',
      'email' => 'seller@example.com',
      'password' => 'password123',
      'role' => UserRole::SELLER,
    ]);
    $category = Category::create(['title' => 'Electronics']);
    $subCategory = SubCategory::create([
      'title' => 'Phones',
      'category_id' => $category->id,
    ]);

    $this->actingAs($seller)
      ->post(route('seller.products.store'), [
        'title' => 'Test Phone',
        'description' => 'A phone prepared for the product feature test.',
        'price' => '199.99',
        'discount_amount' => 20,
        'quantity' => 10,
        'cover_image' => UploadedFile::fake()->createWithContent(
          'cover.png',
          base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        ),
        'sub_categories' => [$subCategory->id],
        'attributes' => [
          ['key' => 'Color', 'value' => 'Black'],
          ['key' => 'Storage', 'value' => '256 GB'],
        ],
      ])
      ->assertRedirect(route('seller.products.index'))
      ->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
      'seller_id' => $seller->id,
      'title' => 'Test Phone',
      'discount_amount' => 20,
    ]);

    $product = $seller->products()->firstOrFail();
    $this->assertSame(179.99, $product->price_after_discount);
    $this->assertDatabaseHas('product_sub_category', [
      'product_id' => $product->id,
      'sub_category_id' => $subCategory->id,
    ]);
    $this->assertDatabaseHas('product_attributes', [
      'product_id' => $product->id,
      'key' => 'color',
      'value' => 'Black',
    ]);
    $this->assertDatabaseHas('product_attributes', [
      'product_id' => $product->id,
      'key' => 'storage',
      'value' => '256 GB',
    ]);
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $product->cover_image));
  }

  public function test_seller_can_view_their_product_details(): void
  {
    $seller = User::create([
      'first_name' => 'Details',
      'last_name' => 'Seller',
      'email' => 'details-seller@example.com',
      'password' => 'password123',
      'role' => UserRole::SELLER,
    ]);
    $category = Category::create(['title' => 'Electronics']);
    $subCategory = SubCategory::create([
      'title' => 'Laptops',
      'category_id' => $category->id,
    ]);
    $product = $seller->products()->create([
      'title' => 'Creator Laptop',
      'description' => 'A detailed description for the product details page.',
      'cover_image' => '/storage/products/covers/laptop.png',
      'price' => 1500,
      'discount_amount' => 100,
      'quantity' => 6,
    ]);
    $product->sub_categories()->attach($subCategory);
    $product->attributes()->create(['key' => 'ram', 'value' => '32 GB']);
    $product->pictures()->create(['picture' => '/storage/products/gallery/laptop-side.png']);

    $this->actingAs($seller)
      ->get(route('seller.products.index'))
      ->assertOk()
      ->assertSee(route('seller.products.show', $product), false);

    $this->actingAs($seller)
      ->get(route('seller.products.show', $product))
      ->assertOk()
      ->assertSee('Creator Laptop')
      ->assertSee('A detailed description for the product details page.')
      ->assertSee('Electronics / Laptops')
      ->assertSee('ram')
      ->assertSee('32 GB')
      ->assertSee('1,400.00');
  }

  public function test_seller_can_edit_their_product_and_replace_attributes(): void
  {
    $seller = User::create([
      'first_name' => 'Seller',
      'last_name' => 'User',
      'email' => 'edit-seller@example.com',
      'password' => 'password123',
      'role' => UserRole::SELLER,
    ]);
    $category = Category::create(['title' => 'Clothes']);
    $subCategory = SubCategory::create([
      'title' => 'Shirts',
      'category_id' => $category->id,
    ]);
    $product = $seller->products()->create([
      'title' => 'Old Shirt',
      'description' => 'The original product description.',
      'cover_image' => '/storage/products/covers/old.png',
      'price' => 100,
      'discount_amount' => 0,
      'quantity' => 4,
    ]);
    $product->sub_categories()->attach($subCategory);
    $product->attributes()->create(['key' => 'color', 'value' => 'Red']);

    $this->actingAs($seller)
      ->get(route('seller.products.edit', $product))
      ->assertOk()
      ->assertSee('Old Shirt');

    $this->actingAs($seller)
      ->put(route('seller.products.update', $product), [
        'title' => 'Updated Shirt',
        'description' => 'The updated product description.',
        'price' => 120,
        'discount_amount' => 10,
        'quantity' => 8,
        'sub_categories' => [$subCategory->id],
        'attributes' => [
          ['key' => 'Size', 'value' => 'Large'],
          ['key' => 'Material', 'value' => 'Cotton'],
        ],
      ])
      ->assertRedirect(route('seller.products.index'))
      ->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
      'id' => $product->id,
      'title' => 'Updated Shirt',
      'price' => 120,
      'discount_amount' => 10,
      'quantity' => 8,
    ]);
    $this->assertDatabaseMissing('product_attributes', [
      'product_id' => $product->id,
      'key' => 'color',
    ]);
    $this->assertDatabaseHas('product_attributes', [
      'product_id' => $product->id,
      'key' => 'size',
      'value' => 'Large',
    ]);
    $this->assertDatabaseHas('product_attributes', [
      'product_id' => $product->id,
      'key' => 'material',
      'value' => 'Cotton',
    ]);
  }

  public function test_seller_can_delete_their_product_and_its_images(): void
  {
    Storage::fake('public');
    Storage::disk('public')->put('products/covers/delete-cover.png', 'cover');
    Storage::disk('public')->put('products/gallery/delete-gallery.png', 'gallery');

    $seller = User::create([
      'first_name' => 'Seller',
      'last_name' => 'User',
      'email' => 'delete-seller@example.com',
      'password' => 'password123',
      'role' => UserRole::SELLER,
    ]);
    $product = $seller->products()->create([
      'title' => 'Product To Delete',
      'description' => 'This product will be deleted by its seller.',
      'cover_image' => '/storage/products/covers/delete-cover.png',
      'price' => 50,
      'discount_amount' => 0,
      'quantity' => 2,
    ]);
    $product->pictures()->create([
      'picture' => '/storage/products/gallery/delete-gallery.png',
    ]);

    $this->actingAs($seller)
      ->delete(route('seller.products.destroy', $product))
      ->assertRedirect(route('seller.products.index'))
      ->assertSessionHas('success');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing('products/covers/delete-cover.png');
    Storage::disk('public')->assertMissing('products/gallery/delete-gallery.png');
  }

  public function test_seller_cannot_manage_another_sellers_product(): void
  {
    $owner = User::create([
      'first_name' => 'Owner',
      'last_name' => 'Seller',
      'email' => 'owner@example.com',
      'password' => 'password123',
      'role' => UserRole::SELLER,
    ]);
    $otherSeller = User::create([
      'first_name' => 'Other',
      'last_name' => 'Seller',
      'email' => 'other@example.com',
      'password' => 'password123',
      'role' => UserRole::SELLER,
    ]);
    $product = $owner->products()->create([
      'title' => 'Private Product',
      'description' => 'Only its owner should be able to manage it.',
      'cover_image' => '/storage/products/covers/private.png',
      'price' => 75,
      'discount_amount' => 0,
      'quantity' => 3,
    ]);

    $this->actingAs($otherSeller)
      ->get(route('seller.products.show', $product))
      ->assertNotFound();

    $this->actingAs($otherSeller)
      ->get(route('seller.products.edit', $product))
      ->assertNotFound();

    $this->actingAs($otherSeller)
      ->delete(route('seller.products.destroy', $product))
      ->assertNotFound();

    $this->assertDatabaseHas('products', ['id' => $product->id]);
  }
}
