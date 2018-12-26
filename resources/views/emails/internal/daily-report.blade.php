@extends('emails.primary', ["title"=>$title])

@section('content')
    <p style="font-size: 9px;">as at {{date("jS F, Y \a\\t h:ia")}}</p>

    <p style="font-weight: bold; font-size: 16px;line-height: 0em;margin: 20px 0px 10px 0px;"><strong>Products ({{number_format(@$report_data["products_total"],0)}})</strong></p>
    <table border="0" cellpadding="4" cellspacing="0">
        <tr>
            <th style="font-weight: bold;text-align: left;">Market</th>
            <th style="font-weight: bold;text-align: left;">Items</th>
            <th style="font-weight: bold;text-align: left;">Offered</th>
            <th style="font-weight: bold;text-align: left;">Listed</th>
            <th style="font-weight: bold;text-align: left;">Listed w/Qty</th>
            <th style="font-weight: bold;text-align: left;">Last Full Sync</th>
        </tr>
        @foreach(@$report_data["markets"] as $k=>$market)
        <tr>
            <td nowrap="nowrap">{{$market->market}}</td>
            <td nowrap="nowrap">{{number_format($market->items,0)}}</td>
            <td nowrap="nowrap">{{number_format($market->offered,0)}}</td>
            <td nowrap="nowrap">{{number_format($market->listed,0)}}</td>
            <td nowrap="nowrap">{{number_format($market->listed_with_qty,0)}}</td>
            <td nowrap="nowrap">{{date("d/m/Y H:i:s",strtotime($market->min_synced_at))}}</td>
        </tr>
        @endforeach
    </table>

    <p style="font-weight: bold; font-size: 16px;line-height: 0em;margin: 20px 0px 10px 0px;"><strong>System</strong></p>
    <table border="0" cellpadding="4" cellspacing="0">
        <tr>
            <th style="font-weight: bold;text-align: left;">Uptime</th>
            <th style="font-weight: bold;text-align: left;">Disk</th>
            <th style="font-weight: bold;text-align: left;">Permissions</th>
        </tr>
        <tr>
            <td nowrap="nowrap">{{$report_data["system"]["uptime"]}}</td>
            <td nowrap="nowrap">{{$report_data["system"]["disk_used_percent"]}}</td>
            <td nowrap="nowrap">N/A</td>
        </tr>
    </table>
@endsection