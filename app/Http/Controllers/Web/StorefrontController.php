<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('sub_categories.category')
            ->withSum('variants', 'stock')
            ->latest()
            ->paginate(12);

        return view('welcome', compact('products'));
    }

    public function search(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:sub_category,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_rating' => ['nullable', 'numeric', 'between:1,5'],
            'in_stock' => ['nullable', 'boolean'],
            'on_sale' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['latest', 'price_asc', 'price_desc', 'rating', 'popular'])],
        ]);

        $validator->after(function ($validator) use ($request) {
            $minPrice = $request->query('min_price');
            $maxPrice = $request->query('max_price');

            if (is_numeric($minPrice) && is_numeric($maxPrice) && (float) $maxPrice < (float) $minPrice) {
                $validator->errors()->add('max_price', 'يجب أن يكون الحد الأقصى للسعر أكبر من أو يساوي الحد الأدنى.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('products.search')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();

        $filters['q'] = trim($filters['q'] ?? '');
        $filters['sort'] = $filters['sort'] ?? 'latest';

        $products = Product::query()
            ->where('is_active', true)
            ->with('sub_categories.category')
            ->withSum('variants', 'stock')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = "%{$filters['q']}%";

                $query->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhereHas('attributes', function ($query) use ($like) {
                            $query->where('key', 'like', $like)
                                ->orWhere('value', 'like', $like);
                        })
                        ->orWhereHas('sub_categories', function ($query) use ($like) {
                            $query->where('title', 'like', $like)
                                ->orWhereHas('category', fn ($query) => $query->where('title', 'like', $like));
                        });
                });
            })
            ->when(! empty($filters['category_id']), function ($query) use ($filters) {
                $query->whereHas('sub_categories', function ($query) use ($filters) {
                    $query->where('category_id', $filters['category_id']);
                });
            })
            ->when(! empty($filters['sub_category_id']), function ($query) use ($filters) {
                $query->whereHas('sub_categories', function ($query) use ($filters) {
                    $query->where('sub_category.id', $filters['sub_category_id']);
                });
            })
            ->when(isset($filters['min_price']), function ($query) use ($filters) {
                $query->whereRaw(
                    '(price - discount_amount) >= CAST(? AS DECIMAL(12, 2))',
                    [$filters['min_price']]
                );
            })
            ->when(isset($filters['max_price']), function ($query) use ($filters) {
                $query->whereRaw(
                    '(price - discount_amount) <= CAST(? AS DECIMAL(12, 2))',
                    [$filters['max_price']]
                );
            })
            ->when(isset($filters['min_rating']), function ($query) use ($filters) {
                $query->where('rating_avg', '>=', $filters['min_rating']);
            })
            ->when(! empty($filters['in_stock']), fn ($query) => $query->whereHas(
                'variants',
                fn ($query) => $query->where('stock', '>', 0)
            ))
            ->when(! empty($filters['on_sale']), fn ($query) => $query->where('discount_amount', '>', 0))
            ->when($filters['sort'] === 'price_asc', fn ($query) => $query->orderByRaw('(price - discount_amount) asc'))
            ->when($filters['sort'] === 'price_desc', fn ($query) => $query->orderByRaw('(price - discount_amount) desc'))
            ->when($filters['sort'] === 'rating', fn ($query) => $query->orderByDesc('rating_avg')->orderByDesc('rating_count'))
            ->when($filters['sort'] === 'popular', fn ($query) => $query->orderByDesc('sold_count'))
            ->when($filters['sort'] === 'latest', fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->with(['sub_categories' => fn ($query) => $query->orderBy('title')])
            ->orderBy('title')
            ->get();

        return view('storefront.search', compact('products', 'categories', 'filters'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('storefront.products.show', [
            'product' => $product->load('attributes', 'sub_categories.category', 'pictures', 'variants'),
        ]);
    }
}
