@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Active Items</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">SKU</th>
                                    <th>Name</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Cost</th>
                                    <th class="text-right">RRP</th>
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
                                        <td><a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank" title="View on eBay">{{$item->Name}}</a></td>
                                        <td class="text-center">{{number_format($item->QTY,0)}}</td>
                                        <td class="text-right">{{"$".number_format($item->Cost,2)}}</td>
                                        <td class="text-right">{{"$".number_format($item->RRP,2)}}</td>
                                        <td class="text-right">{{"$".number_format($item->listing_price,2)}}</td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Options
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="/ebay/preview/?id={{$item->id}}" target="_blank">Preview</a>
                                                    <a class="dropdown-item" href="{{route("resync",$item->id)}}">Force Sync</a>
                                                    <a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank">View on eBay</a>
                                                    <a class="dropdown-item" href="https://bulksell.ebay.com.au/ws/eBayISAPI.dll?SingleList&sellingMode=ReviseItem&ReturnURL=https%3A%2F%2Fwww.ebay.com.au%2Fsh%2Flst%2Factive&lineID={{$item->listingID}}" target="_blank">Edit on eBay</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>

                        <div class="dataTables_wrapper">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="dataTables_info" id="dataTables-example_info" role="status" aria-live="polite">Showing {{$items->firstItem()}} to {{$items->lastItem()}} of {{$items->total()}} products</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dataTables_paginate paging_simple_numbers" id="dataTables-dataTables-example_paginate" role="status" aria-live="polite">
                                        {{ $items->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
