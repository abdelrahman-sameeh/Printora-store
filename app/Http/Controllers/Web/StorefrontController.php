<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        $products = $this->productsQuery()
            ->latest()
            ->paginate(12);

        return view('welcome', compact('products'));
    }

    public function stores(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $query = trim($validated['q'] ?? '');
        $terms = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $sellers = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', RoleName::SELLER->value))
            ->withCount([
                'products as active_products_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->when($terms !== [], function ($query) use ($terms) {
                foreach ($terms as $term) {
                    $like = "%{$term}%";

                    $query->where(function ($query) use ($like) {
                        $query->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like);
                    });
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.stores', compact('sellers', 'query'));
    }

    public function sellerStore(User $seller): View
    {
        $this->ensureSeller($seller);

        $products = $this->productsQuery($seller)
            ->latest()
            ->paginate(12);

        return view('welcome', [
            'products' => $products,
            'storeSeller' => $seller,
        ]);
    }

    public function search(Request $request): View|RedirectResponse
    {
        return $this->searchStore($request);
    }

    public function sellerSearch(Request $request, User $seller): View|RedirectResponse
    {
        $this->ensureSeller($seller);

        return $this->searchStore($request, $seller);
    }

    public function show(Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        return redirect()->route('stores.products.show', [$product->seller_id, $product]);
    }

    public function sellerProduct(User $seller, Product $product): View
    {
        $this->ensureSeller($seller);
        abort_unless($product->seller_id === $seller->id, 404);

        return $this->productView($product, $seller);
    }

    private function searchStore(Request $request, ?User $seller = null): View|RedirectResponse
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
            $route = $seller
                ? route('stores.search', $seller)
                : route('products.search');

            return redirect($route)
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $validator->validated();
        $filters['q'] = trim($filters['q'] ?? '');
        $filters['sort'] = $filters['sort'] ?? 'latest';

        $products = $this->productsQuery($seller)
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
            ->when($seller, function ($query) use ($seller) {
                $query->whereHas('sub_categories.products', function ($query) use ($seller) {
                    $query->where('seller_id', $seller->id)
                        ->where('is_active', true);
                });
            })
            ->with(['sub_categories' => function ($query) use ($seller) {
                $query->when($seller, function ($query) use ($seller) {
                    $query->whereHas('products', function ($query) use ($seller) {
                        $query->where('seller_id', $seller->id)
                            ->where('is_active', true);
                    });
                })->orderBy('title');
            }])
            ->orderBy('title')
            ->get();

        return view('storefront.search', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'storeSeller' => $seller,
        ]);
    }

    private function productsQuery(?User $seller = null)
    {
        return Product::query()
            ->where('is_active', true)
            ->when($seller, fn ($query) => $query->where('seller_id', $seller->id))
            ->with('sub_categories.category')
            ->withSum('variants', 'stock');
    }

    private function productView(Product $product, ?User $seller = null): View
    {
        abort_unless($product->is_active, 404);

        return view('storefront.products.show', [
            'product' => $product->load('attributes', 'sub_categories.category', 'pictures', 'variants'),
            'storeSeller' => $seller,
        ]);
    }

    private function ensureSeller(User $seller): void
    {
        abort_unless($seller->hasRole(RoleName::SELLER), 404);
    }
}
