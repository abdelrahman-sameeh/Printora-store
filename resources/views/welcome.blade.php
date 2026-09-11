@extends('layouts.app')

@section('title', 'Printora | تسوق ببساطة')

@section('content')
    <div class="container py-5">
        <div class="row align-items-center g-5 py-lg-5">
            <div class="col-lg-6">
                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2 mb-4">متجر متعدد
                    البائعين</span>
                <h1 class="display-3 fw-bold lh-sm mb-4">كل ما تبحث عنه، <span class="text-brand">أقرب مما تتخيل.</span>
                </h1>
                <p class="lead text-muted-custom mb-4">اكتشف منتجات من بائعين مختلفين، اجمعها في سلة واحدة، وتابع طلباتك
                    بسهولة من حسابك.</p>

                <form class="mb-4" method="GET" action="{{ route('products.search') }}" role="search">
                    <label class="visually-hidden" for="product-search">ابحث عن منتج</label>
                    <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden">
                        <input class="form-control border-0" id="product-search" name="q" type="search"
                            placeholder="ابحث بالاسم أو الوصف أو الخاصية..." maxlength="100">
                        <button class="btn btn-brand px-4" type="submit">بحث</button>
                    </div>
                    @error('q')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </form>

                <div class="d-flex flex-wrap gap-3">
                    @auth
                        <a class="btn btn-brand px-4" href="{{ route('dashboard') }}">الذهاب إلى حسابي</a>
                    @else
                        <a class="btn btn-brand px-4" href="{{ route('register') }}">ابدأ الآن</a>
                        <a class="btn btn-outline-dark px-4" href="{{ route('login') }}">لدي حساب</a>
                    @endauth
                </div>
            </div>

            <div class="col-lg-6">
                <div class="auth-card p-4 p-md-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <small class="text-muted-custom">تجربة تسوق أسهل</small>
                            <h2 class="h4 fw-bold mb-0 mt-1">مصممة لكل احتياجاتك</h2>
                        </div>
                        <span class="brand-mark">P</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="rounded-4 bg-primary-subtle p-4 h-100">
                                <div class="fs-2 mb-3">🛍️</div>
                                <h3 class="h6 fw-bold">منتجات متنوعة</h3>
                                <small class="text-muted-custom">من أكثر من بائع</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-4 bg-success-subtle p-4 h-100">
                                <div class="fs-2 mb-3">🏷️</div>
                                <h3 class="h6 fw-bold">عروض وكوبونات</h3>
                                <small class="text-muted-custom">خصومات مخصصة</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="rounded-4 bg-dark text-white p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="h6 fw-bold mb-1">تابع طلباتك أولاً بأول</h3>
                                    <small class="text-white-50">من التأكيد وحتى اكتمال التوصيل</small>
                                </div>
                                <span class="fs-2">📦</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="pt-4 pb-5" id="products">
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2 mb-4">
                <div>
                    <span class="text-brand fw-semibold">أحدث المنتجات</span>
                    <h2 class="fw-bold mb-1">اكتشف منتجات المتجر</h2>
                    <p class="text-muted-custom mb-0">تصفّح أحدث المنتجات المتاحة من البائعين.</p>
                </div>

                <a class="btn btn-outline-primary" href="{{ route('products.search') }}">كل المنتجات والفلاتر</a>
            </div>

            @if ($products->isEmpty())
                <div class="dashboard-card card border-0">
                    <div class="card-body text-center p-5">
                        <div class="fs-1 mb-3">🔍</div>
                        <h3 class="h4 fw-bold">لا توجد منتجات متاحة بعد</h3>
                        <p class="text-muted-custom mb-0">ستظهر أحدث المنتجات هنا بمجرد إضافتها.</p>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <article class="dashboard-card card border-0 h-100 overflow-hidden position-relative">
                                <a href="{{ route('products.show', $product) }}">
                                    @if ($product->cover_image)
                                        <img class="w-100" style="height: 210px; object-fit: cover;"
                                            src="{{ $product->cover_image_url }}" alt="{{ $product->title }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light text-muted"
                                            style="height: 210px;">لا توجد صورة</div>
                                    @endif
                                </a>

                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="mb-2">
                                        @foreach ($product->sub_categories->take(2) as $subCategory)
                                            <span class="badge text-bg-light border text-dark mb-1">{{ $subCategory->title }}</span>
                                        @endforeach
                                    </div>
                                    <h3 class="h5 fw-bold mb-2">
                                        <a class="text-dark text-decoration-none stretched-link"
                                            href="{{ route('products.show', $product) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                    <p class="text-muted-custom small mb-3">{{ Str::limit($product->description, 80) }}</p>

                                    <div class="d-flex align-items-end justify-content-between gap-2 mt-auto position-relative z-2">
                                        <div>
                                            @if ($product->discount_amount > 0)
                                                <small class="text-muted-custom text-decoration-line-through d-block">
                                                    {{ number_format($product->price, 2) }} جنيه
                                                </small>
                                            @endif
                                            <strong class="text-brand">{{ number_format($product->price_after_discount, 2) }} جنيه</strong>
                                        </div>
                                        <a class="btn btn-brand" href="{{ route('products.show', $product) }}">عرض</a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $products->fragment('products')->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
