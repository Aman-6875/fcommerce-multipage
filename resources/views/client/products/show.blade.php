
@extends('layouts.client')

@section('content')
<div class="main_content_iner overly_inner ">
    <div class="container-fluid p-0 ">
        <!-- page title  -->
        <div class="row">
            <div class="col-12">
                <div class="page_title_box d-flex align-items-center justify-content-between">
                    <div class="page_title_left">
                        <h3 class="f_s_30 f_w_700 text_white">Product Details</h3>
                        <ol class="breadcrumb page_bradcam mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('client.products.index') }}">Products</a></li>
                            <li class="breadcrumb-item active">Product Details</li>
                        </ol>
                    </div>
                    <a href="{{ route('client.products.edit', $product->id) }}" class="white_btn3">Edit Product</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="white_card position-relative mb_20">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 align-self-center">
                                <img src="{{ asset('storage/' . $product->image_url) ?? 'img/products/01.png' }}" alt="" class="mx-auto d-block sm_w_100" height="300" />
                            </div>
                            <!--end col-->
                            <div class="col-lg-6 align-self-center">
                                <div class="single-pro-detail">
                                    <p class="mb-1">{{ $product->category }}</p>
                                    <div class="custom-border mb-3"></div>
                                    <h3 class="pro-title">{{ $product->name }}</h3>
                                    <p class="text-muted mb-0">{{ $product->description }}</p>
                                    <h2 class="pro-price">
                                        ${{ $product->sale_price ?? $product->price }} 
                                        @if ($product->sale_price)
                                            <span><del>${{ $product->price }}</del></span>
                                            <span class="text-danger fw-bold ms-2">{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% Off</span>
                                        @endif
                                    </h2>
                                    <h6 class="text-muted font_s_13 mt-2 mb-1">Features :</h6>
                                    <ul class="list-unstyled pro-features border-0">
                                        <li>SKU: {{ $product->sku ?? 'N/A' }}</li>
                                        <li>Stock: {{ $product->stock_quantity }}</li>
                                        <li>Weight: {{ $product->weight ?? 'N/A' }}</li>
                                        <li>Active: {{ $product->is_active ? 'Yes' : 'No' }}</li>
                                        <li>Track Stock: {{ $product->track_stock ? 'Yes' : 'No' }}</li>
                                    </ul>
                                    <div class="quantity mt-3">
                                        <a href="{{ $product->product_link }}" class="btn green_bg text-white px-4 d-inline-block " target="_blank">
                                            View Product
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end card-body-->
                </div>
                <!--end card-->
            </div>
            <!--end col-->
        </div>
    </div>
</div>
@endsection
