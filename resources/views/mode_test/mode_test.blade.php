@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Test Mode Products
                    <a href="#" class="btn btn-xs pull-right" data-toggle="modal" data-target="#exampleModalCenter"><i class="fas fa-plus-square"></i></a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-danger text-center">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>The following products are in TEST mode and should not appear on eBay.</p>

                    <div>
                        <!-- Modal -->
                        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Create Test Product</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form method="post" action="{{route('create-test-product')}}">
                                        {!! csrf_field()!!}
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="usr">SKU:</label>
                                                <input type="text" class="form-control" id="sku" name="sku" placeholder="Enter the SKU product">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="{{route('update-mode-test')}}">
                    {!! csrf_field() !!}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center"><input type="checkbox" name="checkall" class="checkall" /></th>
                                <th class="text-center">ID</th>
                                <th class="text-center">SKU</th>
                                <th>Name</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Cost</th>
                                <th class="text-right">RRP</th>
                                <th class="text-right">Listing Price</th>
                                <th class="text-right">Offer ID</th>
                                <th class="text-right">Listing ID</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key => $item)
                                <tr>
                                    <th class="text-center"><input type="checkbox" name="check[]" value="{{$item->id}}" class="mode_test" /></th>
                                    <td class="text-center">{{$item->id}}</td>
                                    <td class="text-center">{{$item->SKU}}</td>
                                    <td>{{$item->Name}}</td>
                                    <td class="text-center">{{number_format($item->QTY,0)}}</td>
                                    <td class="text-right">{{"$".number_format($item->Cost,2)}}</td>
                                    <td class="text-right">{{"$".number_format($item->RRP,2)}}</td>
                                    <td class="text-right">{{"$".number_format($item->listing_price,2)}}</td>
                                    <td class="text-center">{{$item->offerID}}</td>
                                    <td class="text-center">{{$item->listingID}}</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Options
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="/ebay/preview/?id={{$item->id}}" target="_blank">Preview</a>
                                                @if($item->listingID)
                                                    <a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank">View on eBay</a>
                                                    <a class="dropdown-item" href="https://bulksell.ebay.com.au/ws/eBayISAPI.dll?SingleList&sellingMode=ReviseItem&ReturnURL=https%3A%2F%2Fwww.ebay.com.au%2Fsh%2Flst%2Factive&lineID={{$item->listingID}}" target="_blank">Edit on eBay</a>
                                                @else
                                                    <a class="dropdown-item" href="{{route('del-product-test',['id'=>$item->id])}}" onclick="return(confirm('Are you sure you want to delete this item?'));">Delete</a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot id="update-options">
                        <tr>
                            <th class="text-left" colspan="11"><button class="btn btn-sm btn-success" type="submit" id="btn-live">Mark Live</button></th>
                        </tr>
                        </tfoot>
                    </table>
                    </form>

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
<script type="application/javascript">
    $(function(){
        $.checkUpdated=function(){
            var cnt=$(".mode_test:checked").length;
            if(cnt>0){
                var plural=(cnt>1)?"s":"";
                $("#btn-live").text("Mark "+$(".mode_test:checked").length+" Product"+plural+" Live");
                $("#update-options").show();
            }else{
                $("#update-options").hide();
            }
        };
        $(".checkall").click(function(){
            if($(this).is(":checked")){
                $(".mode_test").prop("checked",true);
            }else{
                $(".mode_test").prop("checked",false);
            }
            $.checkUpdated();
        });
        $(".mode_test").click(function(){
            $.checkUpdated();
        });
        $.checkUpdated();
    });
</script>
@endsection
