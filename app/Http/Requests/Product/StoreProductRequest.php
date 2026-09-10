<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => "required|string|max:50",
            "description" => "required|string|max:1000",
            "cover_image" => "required|image|mimes:jpg,jpeg,png,webp|max:2048",
            "price" => "required|numeric|min:0",
            "discount_amount" => "nullable|numeric|min:0|lte:price",
            "quantity" => "required|integer|min:0",
            "product_pictures" => "nullable|array",
            "product_pictures.*" => "image|mimes:jpg,jpeg,png,webp|max:2048",
            "sub_categories" => "required|array|min:1",
            "sub_categories.*" => "exists:sub_category,id",
            "attributes" => "sometimes|array|nullable",
            "attributes.*.key" => "required|string|max:50",
            "attributes.*.value" => "required|string|max:50",
        ];
    }
}
