<?php
namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      "title" => "sometimes|string|min:3|max:50",
      "description" => "sometimes|string|min:10|max:1000",
      "cover_image" => "sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048",
      "price" => "sometimes|numeric|min:0",
      "discount_amount" => "sometimes|nullable|numeric|min:0|lte:price",
      "quantity" => "sometimes|integer|min:0",
      "product_pictures" => "sometimes|nullable|array",
      "product_pictures.*" => "image|mimes:jpg,jpeg,png,webp|max:2048",
      "sub_categories" => "sometimes|array|min:1",
      "sub_categories.*" => "exists:sub_category,id",
      "attributes" => "sometimes|array|nullable",
      "attributes.*.key" => "required|string|max:50",
      "attributes.*.value" => "required|string|max:50",
    ];
  }


}
