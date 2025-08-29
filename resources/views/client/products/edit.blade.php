@extends('layouts.client')

@section('content')
<div class="main_content_iner ">
    <div class="container-fluid p-0 sm_padding_15px">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">Edit Product</h3>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('client.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="name">Product Name</label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter product name" value="{{ old('name', $product->name) }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" name="description" id="description" rows="3" placeholder="Enter product description">{{ old('description', $product->description) }}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price (৳)</label>
                                <input type="number" step="0.01" class="form-control" name="price" id="price" placeholder="Enter price" value="{{ old('price', $product->price) }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Product Image</label>
                                <input type="file" class="form-control-file" name="image" id="image" accept="image/*" onchange="previewImage(this)">
                                <small class="form-text text-muted">Upload JPG, PNG, GIF, or WEBP image. Max size: 2MB (Leave empty to keep current image)</small>
                                
                                @if ($product->image_url)
                                    <div class="mt-2">
                                        <p><strong>Current Image:</strong></p>
                                        <img src="{{ $product->image_url }}" alt="Current Product Image" class="img-thumbnail" width="150">
                                    </div>
                                @endif
                                
                                <div id="imagePreview" class="mt-2" style="display: none;">
                                    <p><strong>New Image Preview:</strong></p>
                                    <img id="preview" src="" alt="Image Preview" class="img-thumbnail" width="150">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="product_link">Product Link</label>
                                <input type="url" class="form-control" name="product_link" id="product_link" placeholder="https://your-product-link.com" value="{{ old('product_link', $product->product_link) }}">
                            </div>
                            <div class="form-group">
                                <label for="facebook_page_id">Facebook Page</label>
                                <select class="form-control" name="facebook_page_id" id="facebook_page_id" required>
                                    <option value="">Select a Facebook page</option>
                                    @foreach ($facebookPages as $page)
                                        <option value="{{ $page->id }}" {{ old('facebook_page_id', $product->facebook_page_id) == $page->id ? 'selected' : '' }}>{{ $page->page_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Product is active
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Update Product</button>
                                <a href="{{ route('client.products.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        document.getElementById('imagePreview').style.display = 'none';
    }
}
</script>
@endsection