<?php

namespace App\Services;

use App\Exceptions\ProductAlreadyExistsException;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function create(User $seller, array $data): Product
    {
        $existingProduct = $seller->products()
            ->whereSlug(Str::slug($data['title']))
            ->first();

        if ($existingProduct) {
            throw new ProductAlreadyExistsException($existingProduct);
        }

        return DB::transaction(function () use ($seller, $data) {
            $product = $seller->products()->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'quantity' => collect($data['variants'])->sum('quantity'),
            ]);

            /** @var UploadedFile $coverImage */
            $coverPath = $data['cover_image']->store('products/covers', 'public');
            $product->update(['cover_image' => Storage::url($coverPath)]);

            if (! empty($data['product_pictures'])) {
                $product->pictures()->createMany(
                    collect($data['product_pictures'])
                        ->map(fn (UploadedFile $image) => [
                            'picture' => Storage::url($image->store('products/gallery', 'public')),
                        ])
                        ->all()
                );
            }

            $product->sub_categories()->sync($data['sub_categories'] ?? []);
            $product->variants()->createMany($this->variantRows($data['variants']));

            foreach ($data['attributes'] ?? [] as $attribute) {
                $product->attributes()->create([
                    'key' => strtolower($attribute['key']),
                    'value' => $attribute['value'],
                ]);
            }

            return $product;
        });
    }

    public function update(User $seller, Product $product, array $data): Product
    {
        if (array_key_exists('discount_amount', $data) && is_null($data['discount_amount'])) {
            $data['discount_amount'] = 0;
        }

        $duplicateProduct = $seller->products()
            ->whereSlug(Str::slug($data['title'] ?? $product->title))
            ->where('id', '!=', $product->id)
            ->first();

        if ($duplicateProduct) {
            throw new ProductAlreadyExistsException($duplicateProduct);
        }

        $oldCoverPath = null;

        if (! empty($data['cover_image'])) {
            /** @var UploadedFile $coverImage */
            $coverImage = $data['cover_image'];
            $coverPath = $coverImage->store('products/covers', 'public');
            $data['cover_image'] = Storage::url($coverPath);
            $oldCoverPath = str_replace('/storage/', '', $product->cover_image);
        }

        $product = DB::transaction(function () use ($product, $data) {
            $product->update(collect($data)->only([
                'title',
                'description',
                'price',
                'discount_amount',
                'cover_image',
            ])->all());

            if (array_key_exists('variants', $data)) {
                $product->variants()->delete();
                $product->variants()->createMany($this->variantRows($data['variants']));
                $product->update(['quantity' => collect($data['variants'])->sum('quantity')]);
            }

            if (array_key_exists('sub_categories', $data)) {
                $product->sub_categories()->sync($data['sub_categories']);
            }

            if (array_key_exists('attributes', $data)) {
                $product->attributes()->delete();

                foreach ($data['attributes'] ?? [] as $attribute) {
                    $product->attributes()->create([
                        'key' => strtolower($attribute['key']),
                        'value' => $attribute['value'],
                    ]);
                }
            }

            if (! empty($data['product_pictures'])) {
                $product->pictures()->createMany(
                    collect($data['product_pictures'])
                        ->map(fn (UploadedFile $image) => [
                            'picture' => Storage::url($image->store('products/gallery', 'public')),
                        ])
                        ->all()
                );
            }

            return $product->refresh();
        });

        if ($oldCoverPath) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        return $product;
    }

    private function variantRows(array $variants): array
    {
        return collect($variants)
            ->map(fn (array $variant): array => [
                'size' => trim($variant['size']),
                'color' => trim($variant['color']),
                'quantity' => $variant['quantity'],
            ])
            ->all();
    }
}
