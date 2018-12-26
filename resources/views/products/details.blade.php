@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>{{$product->sku.": ".$product->name}}</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Product</div>
                    <div class="card-body">
                        <form method="post" action="{{route('unitex-dropbox-product-refresh-upload')}}">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sku">ID:</label>
                                        <span class="form-control" disabled="disabled">{{$product->id}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sku">SKU:</label>
                                        <span class="form-control" disabled="disabled">{{$product->sku}}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="qty">QTY:</label>
                                        <span class="form-control" disabled="disabled">{{$product->qty}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="name">Title:</label>
                                <input type="text" name="name" class="form-control" value="{{$product->name}}">
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea name="description" class="form-control" rows="10">{{$product->description}}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="category">Category:</label>
                                <input type="text" name="category" class="form-control" value="{{$product->category}}">
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cost">Cost:</label>
                                        <input type="text" name="cost" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($product->cost,2)}}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sell">Sell:</label>
                                        <input type="text" name="sell" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($product->sell,2)}}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="rrp">RRP:</label>
                                        <input type="text" name="rrp" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($product->rrp,2)}}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="listing_price">Listing Price:</label>
                                        <input type="text" name="listing_price" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($product->listing_price,2)}}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sku">Source:</label>
                                <span class="form-control" disabled="disabled">{{$source->source}}</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sku">Created:</label>
                                        <span class="form-control" disabled="disabled">{{$product->created_at}}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sku">Updated:</label>
                                        <span class="form-control" disabled="disabled">{{$product->updated_at}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="sub" value="upload" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Images</div>
                    <div class="card-body">
                        @if($images)
                            @foreach($images as $image)
                                <a href="{{$image}}" target="_blank"><img src="{{$image}}" class="img-thumbnail" style="width:49%; margin: 0px 0px 7px;" alt="{{$image}}"></a>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Specifics</div>
                    <div class="card-body">
                        <form method="post" action="{{route('unitex-dropbox-product-refresh-upload')}}">
                            {!! csrf_field() !!}
                            @if($specifics)
                                @foreach($specifics as $name=>$value)
                                    <div class="form-group">
                                        <label for="{{$name}}">{{$name}}:</label>
                                        <span class="form-control" disabled="disabled">{{$value}}</span>
                                    </div>
                                @endforeach
                            @endif
                            <div class="form-group">
                                <button type="submit" name="sub" value="upload" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
