@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Import eBay Orders CSV File
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-danger text-center">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>This tool allows you to upload and import eBay orders from the File Exchange Export on eBay.</p>
                    <p><a href="https://k2b-bulk.ebay.com.au/ws/eBayISAPI.dll?SMDownloadRequest" target="_blank">Click here</a> to download the latest import file.</p>

                    <form method="post" action="{{route('ebay-import-orders-process')}}" enctype="multipart/form-data">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="file">Upload File:</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="sub" value="upload" class="btn btn-primary">Upload &amp; Process</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
