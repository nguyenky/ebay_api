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
                        <a href="/user-dropbox">Dropbox User infor</a>
                    </div>
                    <h2>Main</h2>
                    <form action="{{route('search')}}" method="post">
                        {!! csrf_field() !!}
                        <input type="text" name="path" placeholder="path"></input>
                        <input type="text" name ="mode" placeholder="mode"></input>
                        <input type="text" name ="query" placeholder="query"></input>
                        <button type="submit">Search</button>
                    </form>
                    <div>
                        <h2> Data Search</h2>
                        <table>
                            <tr>
                                <th>Name</th>
                                <th>Download</th> 
                            </tr>
                            @foreach($data['matches'] as $item)
                            <tr>
                                <td>{{$item['metadata']['name']}}</td>
                                <td><a href="/download?path={{ urlencode($item['metadata']['path_lower']) }}">Download</a></td> 
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
