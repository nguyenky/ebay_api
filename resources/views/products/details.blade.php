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

                <div class="card" style="margin: 30px 0px 0px 0px">
                    <div class="card-header">eBay Details</div>
                    <div class="card-body">
                        @if($ebay_details)
                            <form method="post" action="{{route('unitex-dropbox-product-refresh-upload')}}">
                                {!! csrf_field() !!}

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sku">ID:</label>
                                            <span class="form-control" disabled="disabled">{{$ebay_details->id}}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="offerid">OfferID:</label>
                                            <span class="form-control" disabled="disabled">{{$ebay_details->offerid}}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="listingid">ListingID:</label>
                                            <span class="form-control" disabled="disabled">{{$ebay_details->listingid}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="categoryid">CategoryID:</label>
                                            <input type="text" name="categoryid" class="form-control" value="{{$ebay_details->categoryid}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sync">Sync:</label>
                                            <select name="sync" id="sync" class="form-control" size="1">
                                                <option value="0"{!! (old("sync",$ebay_details->sync)<1)?" selected=\"selected\"":"" !!}>No</option>
                                                <option value="1"{!! (old("sync",$ebay_details->sync)>0)?" selected=\"selected\"":"" !!}>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="synced_at">Last Synced:</label>
                                            <span class="form-control" disabled="disabled" title="{{$ebay_details->synced_at}}">{{\Carbon\Carbon::now()->diffForHumans(\Carbon\Carbon::parse($ebay_details->synced_at))}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="margin">Margin:</label>
                                            <input type="text" name="margin" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{number_format($ebay_details->margin,2)."%"}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="shipping">Shipping:</label>
                                            <input type="text" name="shipping" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($ebay_details->shipping,2)}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="sale_cost">CoS:</label>
                                            <input type="text" name="sale_cost" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($ebay_details->sale_cost,2)}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="price">Listing Price:</label>
                                            <input type="text" name="price" class="form-control" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($ebay_details->price,2)}}">
                                        </div>
                                    </div>
                                </div>

                                @if(strlen($ebay_details->error)>0)
                                    <div class="form-group">
                                        <label for="error">Errors:</label>
                                        <textarea name="error" class="form-control" rows="3">{{$ebay_details->error}}</textarea>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sku">Created:</label>
                                            <span class="form-control" disabled="disabled">{{$ebay_details->created_at}}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sku">Updated:</label>
                                            <span class="form-control" disabled="disabled">{{$ebay_details->updated_at}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" name="sub" value="upload" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        @else
                            <p class="alert alert-danger">No eBay Details found.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Images</div>
                    <div class="card-body">
                        @if($images)
                            @foreach($images as $image)
                                <a href="{{$image}}" target="_blank"><img src="{{$image}}" class="img-thumbnail" style="max-width:32%; margin: 0px 0px 7px;" alt="{{$image}}"></a>
                            @endforeach
                        @else
                            <p class="alert alert-danger">Error: No images found.</p>
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
                            <div class="form-group">
                                <button type="submit" name="sub" value="upload" class="btn btn-primary">Update</button>
                            </div>
                            @else
                                <p class="alert alert-danger">Error: No specifics found.</p>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
            </div>
        </div>
    </div>
@endsection
