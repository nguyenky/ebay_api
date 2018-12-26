@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Generic Import Tool :: Options
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-danger text-center">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>Select the following required details to perform the import.</p>

                    <form method="post" action="">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label for="table">Table:</label>
                            <select name="table" class="form-control" size="1">
                                <option value="">Select One</option>
                                @foreach($tables as $table)
                                    <option value="{{$table}}">{{$table}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="sub" value="next" class="btn btn-primary">Next</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        console.log("loaded");
    });
</script>
@endsection
