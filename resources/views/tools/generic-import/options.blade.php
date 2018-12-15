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

                    <form method="post" action="{{route('generic-file-import-tools-options')}}">
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
                            <label for="header_row_y">Header Row:</label>
                            <input type="text" name="header_row_y" id="header_row_y" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label for="header_row_x">Header Cell:</label>
                            <input type="text" name="header_row_x" id="header_row_x" class="form-control" value="0">
                        </div>
                        <div class="form-group" style="overflow: scroll;">
                            <label for="">Data (First 10 Lines):</label>

                            <table class="table table-bordered table-hover">
                                <tbody>
                                @for($c=0;$c<10 && $c<count($rows);$c++)
                                    <tr>
                                        @foreach($rows[$c] as $cell)
                                            <td>{{$cell}}</td>
                                        @endforeach
                                    </tr>
                                @endfor
                                </tbody>
                            </table>
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
        $("#header_row_y,#header_row_x").change(function(){
            var x=$("#header_row_x").val();
            var y=$("#header_row_y").val();
            $("TABLE.table TD").removeClass("alert alert-success");
            var table = $("TABLE.table")[0];
            var cell = table.rows[x].cells[y]; // This is a DOM "TD" element
            var $cell = $(cell); // Now it's a jQuery object.
            $cell.addClass("alert-success").addClass("alert");
            console.log("changed");
        });
        $("#header_row_x").change();
        console.log("loaded");
    });
</script>
@endsection
