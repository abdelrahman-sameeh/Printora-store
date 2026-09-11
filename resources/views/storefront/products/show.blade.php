@extends('layouts.app')

@section('title', $product->title . ' | Printora')

@section('content')
    <div class="container py-5">
        <div class="mb-4">
            <a class="text-brand text-decoration-none fw-semibold" href="{{ route('home') }}#products">
                العودة للمنتجات
            </a>
            <h1 class="fw-bold mt-2 mb-1">{{ $product->title }}</h1>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="dashboard-card card border-0 overflow-hidden">
                    @if ($product->cover_image)
                        <img class="w-100" style="max-height: 520px; object-fit: contain; background: #f8f9fa;"
                            src="{{ $product->cover_image_url }}" alt="{{ $product->title }}">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light text-muted"
                            style="height: 360px;">لا توجد صورة رئيسية</div>
                    @endif
                </div>

                @if ($product->pictures->isNotEmpty())
                    <div class="row g-3 mt-1">
                        @foreach ($product->pictures as $picture)
                            <div class="col-6 col-md-4">
                                <a class="d-block dashboard-card card border-0 overflow-hidden" href="{{ $picture->picture_url }}"
                                    target="_blank" rel="noopener">
                                    <img class="w-100" style="height: 150px; object-fit: cover;" src="{{ $picture->picture_url }}"
                                        alt="صورة إضافية لـ {{ $product->title }}">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-5">
                <div class="dashboard-card card border-0 mb-4">
                    <div class="card-body p-4">
                        @if ($product->discount_amount > 0)
                            <span class="badge text-bg-danger mb-2">خصم {{ number_format($product->discount_amount, 2) }}
                                جنيه</span>
                            <span class="text-muted-custom text-decoration-line-through d-block">
                                {{ number_format($product->price, 2) }} جنيه
                            </span>
                        @endif
                        <strong class="text-brand fs-2 d-block mb-4">
                            {{ number_format($product->price_after_discount, 2) }} جنيه
                        </strong>

                        <div class="d-flex justify-content-between py-3 border-top">
                            <span class="text-muted-custom">المتوفر</span>
                            @if ($product->quantity > 0)
                                <strong>{{ $product->quantity }} قطعة</strong>
                            @else
                                <span class="badge text-bg-secondary">نفد المخزون</span>
                            @endif
                        </div>

                        @auth
                            @if (auth()->user()->hasRole(\App\Enums\RoleName::USER) && $product->quantity > 0)
                                <form class="d-flex gap-2 mt-3" method="POST" action="{{ route('cart.items.store') }}">
                                    @csrf
                                    <input name="product_id" type="hidden" value="{{ $product->id }}">
                                    <input class="form-control" name="quantity" type="number" min="1"
                                        max="{{ $product->quantity }}" value="1" required>
                                    <button class="btn btn-brand flex-shrink-0" type="submit">أضف للسلة</button>
                                </form>
                            @endif
                        @else
                            @if ($product->quantity > 0)
                                <a class="btn btn-brand w-100 mt-3" href="{{ route('login') }}">سجل الدخول للإضافة للسلة</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="dashboard-card card border-0 mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">وصف المنتج</h2>
                        <p class="mb-0" style="white-space: pre-line;">{{ $product->description }}</p>
                    </div>
                </div>

                <div class="dashboard-card card border-0 mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">التصنيفات</h2>
                        @forelse ($product->sub_categories as $subCategory)
                            <span class="badge text-bg-light border text-dark me-1 mb-1">
                                {{ $subCategory->category->title }} / {{ $subCategory->title }}
                            </span>
                        @empty
                            <p class="text-muted-custom mb-0">لا توجد تصنيفات مرتبطة.</p>
                        @endforelse
                    </div>
                </div>

                <div class="dashboard-card card border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">خصائص المنتج</h2>
                        @if ($product->attributes->isEmpty())
                            <p class="text-muted-custom mb-0">لا توجد خصائص مسجلة.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                        @foreach ($product->attributes as $attribute)
                                            <tr>
                                                <th class="ps-0">{{ $attribute->key }}</th>
                                                <td class="text-end pe-0">{{ $attribute->value }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
