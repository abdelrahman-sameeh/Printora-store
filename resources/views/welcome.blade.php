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

                <div class="d-flex flex-wrap gap-3">
                    @auth
                        <a class="btn btn-brand btn-lg px-4" href="{{ route('dashboard') }}">الذهاب إلى حسابي</a>
                    @else
                        <a class="btn btn-brand btn-lg px-4" href="{{ route('register') }}">ابدأ الآن</a>
                        <a class="btn btn-outline-dark btn-lg px-4 rounded-4" href="{{ route('login') }}">لدي حساب</a>
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
    </div>
@endsection