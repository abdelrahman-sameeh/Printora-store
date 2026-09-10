@extends('layouts.app')

@section('title', 'منتجاتي | Printora')

@section('content')
  <div class="container py-5">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
      <div>
        <a class="text-brand text-decoration-none fw-semibold" href="{{ route('dashboard') }}">العودة للحساب</a>
        <h1 class="fw-bold mt-2 mb-1">منتجاتي</h1>
        <p class="text-muted-custom mb-0">إدارة المنتجات التي تعرضها في المتجر.</p>
      </div>
      <a class="btn btn-brand px-4" href="{{ route('seller.products.create') }}">إضافة منتج</a>
    </div>

    @if ($products->isEmpty())
      <div class="dashboard-card card border-0">
        <div class="card-body text-center p-5">
          <h2 class="h4 fw-bold">لا توجد منتجات بعد</h2>
          <p class="text-muted-custom mb-4">ابدأ بإضافة أول منتج ليظهر في متجرك.</p>
          <a class="btn btn-brand px-4" href="{{ route('seller.products.create') }}">إنشاء أول منتج</a>
        </div>
      </div>
    @else
      <div class="row g-4">
        @foreach ($products as $product)
          <div class="col-md-6 col-xl-4">
            <article class="dashboard-card card border-0 h-100 overflow-hidden">
              @if ($product->cover_image)
                <img class="w-100" style="height: 190px; object-fit: cover;" src="{{ $product->cover_image_url }}"
                  alt="{{ $product->title }}">
              @endif
              <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-2">
                  <a class="text-dark text-decoration-none" href="{{ route('seller.products.show', $product) }}">
                    {{ $product->title }}
                  </a>
                </h2>
                <p class="text-muted-custom mb-3">{{ Str::limit($product->description, 100) }}</p>
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <div>
                    @if ($product->discount_amount > 0)
                      <small class="text-muted-custom text-decoration-line-through d-block">
                        {{ number_format($product->price, 2) }}
                      </small>
                    @endif
                    <strong class="text-brand">{{ number_format($product->price_after_discount, 2) }}</strong>
                  </div>
                  @if ($product->discount_amount > 0)
                    <span class="badge text-bg-danger">خصم
                      {{ number_format($product->discount_amount, 2) }} جنيه</span>
                  @endif
                  <span class="text-muted-custom">المخزون: {{ $product->quantity }}</span>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                  <a class="btn btn-brand btn-sm flex-grow-1"
                    href="{{ route('seller.products.show', $product) }}">عرض</a>
                  <a class="btn btn-outline-primary btn-sm flex-grow-1"
                    href="{{ route('seller.products.edit', $product) }}">تعديل</a>
                  <form class="flex-grow-1" method="POST" action="{{ route('seller.products.destroy', $product) }}"
                    onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm w-100" type="submit">حذف</button>
                  </form>
                </div>
              </div>
            </article>
          </div>
        @endforeach
      </div>

      <div class="mt-4">
        {{ $products->links() }}
      </div>
    @endif
  </div>
@endsection
