@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <?php
                        $url = 'https://auth.sandbox.ebay.com/oauth2/authorize?client_id=SFRSoftw-sfrsoftw-SBX-72ccbdeee-fce8a005&response_type=code&redirect_uri=SFR_Software-SFRSoftw-sfrsof-watlbqpzg&scope=https://api.ebay.com/oauth/api_scope https://api.ebay.com/oauth/api_scope/buy.order.readonly https://api.ebay.com/oauth/api_scope/buy.guest.order https://api.ebay.com/oauth/api_scope/sell.marketing.readonly https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.inventory.readonly https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.account.readonly https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly https://api.ebay.com/oauth/api_scope/sell.fulfillment https://api.ebay.com/oauth/api_scope/sell.analytics.readonly https://api.ebay.com/oauth/api_scope/sell.marketplace.insights.readonly https://api.ebay.com/oauth/api_scope/commerce.catalog.readonly';
                    ?>
                    <div>
                        <!-- <a href="{{route('dropbox')}}">Dropbox Dasboard / Get Access token dropbox</a> -->
                    </div>
                    <br />
                    <!-- <div>
                        <a href="{{url($url)}}">Get grant code Ebay</a>
                    </div>
                    <br /> -->
                    <div>
                        <a href="{{route('begin')}}"> Begin process !!</a>
                    </div>
                    <div>
                        <a href="{{route('refresh')}}"> Refresh Token !!</a>
                    </div>
                    <br />
                    <div>
                        <!-- <a href="{{route('getall')}}"> Get All Items !!</a> -->
                        <a href="{{route('getItem')}}"> Get Detail Item !!</a>
                    </div>
                    <br />
                    <div>
                        <!-- <a href="/user-dropbox">Dropbox User infor</a> -->
                    </div>
                    <div>
                        <!-- <a href="/search-file-dropbox">Dropbox User infor</a> -->
                    </div> 
                    <div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>SKU</th>
                                    <th>Name</th>
                                    <th>Cost</th>
                                    <th>Sell</th>
                                    <th>RRP</th>
                                    <th>QTY</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $key => $item)
                                @if($item->listingID)
                                    <tr>
                                        <td>{{$item->id}}</td>
                                        <td>{{$item->SKU}}</td>
                                        <td>{{$item->Name}}</td>
                                        <td>{{$item->Cost}}</td>
                                        <td>{{$item->Sell}}</td>
                                        <td>{{$item->RRP}}</td>
                                        <td>{{$item->QTY}}</td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Options
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="/ebay/preview/?id={{$item->id}}" target="_blank">Preview</a>
                                                    <a class="dropdown-item" href="https://www.ebay.com.au/itm/{{$item->listingID}}" target="_blank">View on eBay</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
