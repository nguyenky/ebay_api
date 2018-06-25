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
                        <a href="/dropbox">Dropbox Dasboard / Get Access token dropbox</a>
                    </div>
                    <br />
                    <div>
                        <a href="/begin"> Begin process !!</a>
                    </div>
                    <br />
                    <div>
                        <!-- <a href="/user-dropbox">Dropbox User infor</a> -->
                    </div>
                    <div>
                        <!-- <a href="/search-file-dropbox">Dropbox User infor</a> -->
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
