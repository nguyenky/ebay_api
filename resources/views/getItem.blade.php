@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                    <br />
                    <div>
                        <a href="{{route('begin')}}"> Begin process !!</a>
                    </div> -->
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
                        @foreach($items as $key => $item)
                            <div class="col-ms-6 col-md-4">
                                <span style="color: #3b7ec4;">{{$key}} : </span>
                                <a href="{{route('getDetail',['id'=>$item['SKU']])}}">{{$item['SKU']}}</a>
                            </div>
                        @endforeach
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
