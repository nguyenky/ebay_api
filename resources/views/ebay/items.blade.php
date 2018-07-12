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
                    <div>
                        @foreach($items as $key => $item)
                            <div class="col-ms-6 col-md-4">
                                <span style="color: #3b7ec4;">{{$key}} : </span>
                                <a href="{{route('createInventory',['slug'=>$key])}}">{{$item['SKU']}}</a>
                            </div>
                        @endforeach
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
