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
                            <!-- Modal -->
                            <div class="modal fade" id="modalForceSync" tabindex="-1" role="dialog" aria-labelledby="modalForceSyncTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalForceSyncTitle">Force Sync</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="get" action="#">
                                            {!! csrf_field()!!}
                                            <div class="modal-body">
                                                <p>Click the "Process" button below to force a sync of ListingID <strong class="listingID"></strong>.</p>
                                                <div class="framed">

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary" data-link="">Process</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">SKU</th>
                                    <th class="text-center">offerID</th>
                                    <th class="text-center">listingID</th>
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
                                <tr>
                                    <td class="text-center">{{$item->id}}</td>
                                    <td class="text-center">{{$item->SKU}}</td>
                                    <td class="text-center">{{$item->offerID}}</td>
                                    <td class="text-center{{($item->listingID>0)?"":" danger"}}">{{$item->listingID}}</td>
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
                                                <a class="dropdown-item sync" href="#" data-toggle="modal" data-target="#modalForceSync" data-id="{{$item->id}}" data-listingID="{{$item->listingID}}">Force Sync</a>
                                                <a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank">View on eBay</a>
                                                <a class="dropdown-item" href="https://bulksell.ebay.com.au/ws/eBayISAPI.dll?SingleList&sellingMode=ReviseItem&ReturnURL=https%3A%2F%2Fwww.ebay.com.au%2Fsh%2Flst%2Factive&lineID={{$item->listingID}}" target="_blank">Edit on eBay</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
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
<script type="application/javascript">
    $(function(){
        $("A.sync").click(function(){
            $("#modalForceSync .listingID").text($(this).data("listingid"));
            $("#modalForceSync .btn-primary").data("link","/resync/"+$(this).data("id"));
        });

        $("#modalForceSync .btn-primary").click(function(){
            var p=$("#modalForceSync .framed");
            var t=$(this);
            t.text("Loading...");
            var frame=$("<iframe></iframe>").attr("src",$(this).data("link")).attr("style","display: block;margin: 20px auto 0px auto;width: 100%;border:1px solid #cccccc;").on("load", function() {
                t.text("Run again");
            });
            $("IFRAME",p).remove();
            p.append(frame);
            return(false);
        });
    });
</script>
@endsection
