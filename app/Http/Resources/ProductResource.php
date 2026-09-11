<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'discount_amount' => $this->discount_amount,
            'price_after_discount' => $this->price_after_discount,
            'quantity' => $this->quantity,
            'variants' => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'size' => $variant->size,
                'color' => $variant->color,
                'quantity' => $variant->quantity,
            ]),
            'cover_image' => url($this->cover_image),
            'sub_categories' => $this->sub_categories->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'title' => $sub->title,
                    'slug' => $sub->slug,
                    'category' => [
                        'id' => $sub->category->id,
                        'title' => $sub->category->title,
                        'slug' => $sub->category->slug,
                    ],
                ];
            }),
            'pictures' => $this->pictures->map(function ($pic) {
                return [
                    'id' => $pic->id,
                    'picture' => url($pic->picture),
                ];
            }),
            'attributes' => $this->attributes->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'key' => $attr->key,
                    'value' => $attr->value,
                ];
            }),
        ];
    }
}
