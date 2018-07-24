@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Dashboard

                    <div class="btn-group float-right">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Tasks
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{route('begin')}}">Begin Process</a>
                            <a class="dropdown-item" href="{{route('refresh')}}">Refresh Token</a>
                            <a class="dropdown-item" href="{{route('getItem')}}">Get Item</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div>
                        <h4>
                            Active Items
                            <a href="#" class="btn pull-right"><i class="fas fa-search"></i></a>
                        </h4>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">SKU</th>
                                    <th>Name</th>
                                    <th class="text-right">Cost</th>
                                    <th class="text-right">RRP</th>
                                    <th class="text-center">QTY</th>
                                    <th class="text-right">Listing Price</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $key => $item)
                                @if($item->listingID)
                                    <tr>
                                        <td class="text-center">{{$item->id}}</td>
                                        <td class="text-center">{{$item->SKU}}</td>
                                        <td>{{$item->Name}}</td>
                                        <td class="text-right">{{"$".number_format($item->Cost,2)}}</td>
                                        <td class="text-right">{{"$".number_format($item->RRP,2)}}</td>
                                        <td class="text-center">{{number_format($item->QTY,0)}}</td>
                                        <td class="text-right">{{"$".number_format($item->listing_price,2)}}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Options
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="/ebay/preview/?id={{$item->id}}" target="_blank">Preview</a>
                                                    <a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank">View on eBay</a>
<<<<<<< HEAD
                                                    <a class="dropdown-item" href="https://bulksell.ebay.com.au/ws/eBayISAPI.dll?SingleList&sellingMode=ReviseItem&ReturnURL=https%3A%2F%2Fwww.ebay.com.au%2Fsh%2Flst%2Factive&lineID={{$item->listingID}}" target="_blank">Edit on eBay</a>
=======
>>>>>>> 3b1b9bf8498cd7fc1a6613526e2c23dde8143949
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
<<<<<<< HEAD
                        {{ $items->links() }}
=======
>>>>>>> 3b1b9bf8498cd7fc1a6613526e2c23dde8143949
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
