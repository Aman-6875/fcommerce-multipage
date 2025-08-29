@extends('layouts.client')

@section('content')
<div class="main_content_iner overly_inner ">
    <div class="container-fluid p-0 ">
        <!-- page title  -->
        <div class="row">
            <div class="col-12">
                <div class="page_title_box d-flex align-items-center justify-content-between">
                    <div class="page_title_left">
                        <h3 class="f_s_30 f_w_700 text_white">Products</h3>
                        <ol class="breadcrumb page_bradcam mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Products</li>
                        </ol>
                    </div>
                    <a href="{{ route('client.products.create') }}" class="white_btn3">Add Product</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">Product List</h3>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($products->isEmpty())
                            <div class="text-center py-5">
                                <h4 class="text-muted">No products found</h4>
                                <p class="text-muted">You haven't added any products yet.</p>
                                <a href="{{ route('client.products.create') }}" class="btn btn-primary">Add Your First Product</a>
                            </div>
                        @else
                        <div class="QA_section">
                            <div class="QA_table mb_30">
                                <table class="table lms_table_active ">
                                    <thead>
                                        <tr>
                                            <th scope="col">Image</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Facebook Page</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                            <tr>
                                                <td>
                                                    @if($product->image_url)
                                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="50" class="img-thumbnail">
                                                    @else
                                                        <span class="badge badge-secondary">No Image</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $product->name }}</strong>
                                                    @if($product->description)
                                                        <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>৳{{ number_format($product->price, 2) }}</strong>
                                                </td>
                                                <td>{{ $product->facebookPage->page_name }}</td>
                                                <td>
                                                    @if($product->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('client.products.show', $product->id) }}" class="btn btn-info btn-sm">View</a>
                                                        <a href="{{ route('client.products.edit', $product->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                                        <form action="{{ route('client.products.destroy', $product->id) }}" method="POST" style="display: inline-block;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.lms_table_active').DataTable();
    });
</script>
@endpush