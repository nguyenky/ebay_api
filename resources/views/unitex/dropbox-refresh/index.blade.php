@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Unitex Dropbox File Import</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p>Go <a href="https://www.dropbox.com/sh/bvyy8cveb54yxdv/AABrKZPcMuyX2M-B9fBAfVVsa/PRODUCT%20INFORMATION%20FILE%20-%20STANDARD?dl=0&subfolder_nav_tracking=1" target="_blank">here to download</a> the Unitex file from Dropbox, then import it manually right here.</p>

                    <form method="post" action="{{route('unitex-dropbox-product-refresh-upload')}}" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="file">Upload File:</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="sub" value="upload" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
