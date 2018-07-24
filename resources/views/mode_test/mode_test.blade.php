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
                        <div class="alert alert-danger text-center">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div>
                    <div class="text-center">
                        <h3>Mode test</h3>
                        <div class="radio mode-test" >
                            <form method="post" action="{{route('update-mode-test')}}">
                                {!! csrf_field() !!}
                                <div>
                                    @if($system->mode_test)
                                    <label><input type="radio" name="mode_test" value="0">Inactive</label>
                                    <label><input type="radio" name="mode_test" checked="" value="1">Active</label>
                                    @else
                                    <label><input type="radio" name="mode_test" checked="" value="0">Inactive</label>
                                    <label><input type="radio" name="mode_test" value="1">Active</label>
                                    @endif
                                </div>
                                
                                <button class="btn btn-success" type="submit">Update</button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
                          Add Test Product
                        </button>

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
                    
                        <h4>Test Items</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">SKU</th>
                                    <th>Name</th>
                                    <th class="text-right">Cost</th>
                                    <th class="text-right">RRP</th>
                                    <th class="text-center">QTY</th>
                                    <th class="text-right">Listing Price</th>
                                    <th class="text-right">Offer ID</th>
                                    <th class="text-right">Listing ID</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $key => $item)
                                    <tr>
                                        <td class="text-center">{{$item->id}}</td>
                                        <td class="text-center">{{$item->SKU}}</td>
                                        <td>{{$item->Name}}</td>
                                        <td class="text-right">{{"$".number_format($item->Cost,2)}}</td>
                                        <td class="text-right">{{"$".number_format($item->RRP,2)}}</td>
                                        <td class="text-center">{{number_format($item->QTY,0)}}</td>
                                        <td class="text-right">{{"$".number_format($item->listing_price,2)}}</td>
                                        <td class="text-center">{{$item->offerID}}</td>
                                        <td class="text-center">{{$item->listingID}}</td>
                                        <td class="text-center">
                                            @if($item->listingID)
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Options
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="/ebay/preview/?id={{$item->id}}" target="_blank">Preview</a>
                                                    <a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank">View on eBay</a>
                                                </div>
                                            </div>
                                            @else
                                                <a class="btn btn-primary" href="{{route('del-product-test',['id'=>$item->id])}}">DELETE</a> 
                                            @endif
                                        </td>
                                    </tr>
                            @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
