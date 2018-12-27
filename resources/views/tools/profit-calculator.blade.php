@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Profit Calculator
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-danger text-center">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>This tools helps to calculate profit from a sale.</p>

                    @if (isset($product) && $product)
                        <div class="alert alert-secondary">
                            The cost is based on product id "{{$product->id}}". The SKU is "{{$product->sku}}", with a title of "{{$product->name}}".
                        </div>
                    @endif

                    <form method="post" action="?">
                        {!! csrf_field() !!}

                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="cost">Cost:</label>
                                    <input type="text" name="cost" id="cost" class="form-control" data-prepend="$" data-append="" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($cost,2)}}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="margin">Margin (%):</label>
                                    <input type="text" name="margin" id="margin" class="form-control" data-prepend="" data-append="%" aria-label="Amount (to the nearest dollar)" value="25.00%">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="shipping">Shipping:</label>
                                    <input type="text" name="shipping" id="shipping" class="form-control" data-prepend="$" data-append="" aria-label="Amount (to the nearest dollar)" value="$27.50">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="sale_cost">CoS (inc GST):</label>
                                    <input type="text" name="sale_cost" id="sale_cost" class="form-control" data-prepend="" data-append="%" aria-label="Amount (to the nearest dollar)" value="20.00%">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="price">Listing Price:</label>
                                    <input type="text" name="price" id="price" class="form-control" data-prepend="$" data-append="" aria-label="Amount (to the nearest dollar)" value="{{"$".number_format($price,2)}}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="price">Profit:</label>
                                    <input type="text" name="profit" id="profit" class="form-control" data-prepend="$" data-append="" aria-label="Amount (to the nearest dollar)" value="$0.00">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" name="sub" value="upload" class="btn btn-primary">Calculate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $.toNumber=function(val){
            return(Number(String(val).replace(/[^\d\\.]+/,"")));
        };
        $.formatNum=function(val){
            return(Number($.toNumber(val)).toFixed(2));
        };
        // $.getCalculation=function(margin,shipping,cos,price){
        // };
        $.calculate=function($t){
            let id=$t.attr("id");

            let cost=$.toNumber($("#cost").val());
            let margin=$.toNumber($("#margin").val())/100;
            let shipping=$.toNumber($("#shipping").val());
            let cos=$.toNumber($("#cos").val())/100;
            let price=$.toNumber($("#price").val());
            let profit=$.toNumber($("#profit").val());
            if(id=="cost"||id=="margin"||id=="shipping"||id=="cos"){
                let cost_margin=cost+(cost*margin);
                let price=cost_margin+shipping+(cost_margin*cos);
                $("#price").val(price);
                $("#profit").val(cost_margin-cost);
            }else if(id=="price"){
                let cost_of_sale=price-cost-shipping;
                let margin=(cost_of_sale-(cost_of_sale*cos))/cost;
                let cost_margin=cost+(cost*margin);
                $("#margin").val(margin*100);
                $("#profit").val(cost_margin-cost);
            }

            $("INPUT").each(function(){
                let nv=$.toNumber($(this).val());
                let prepend=$(this).data("prepend");
                let append=$(this).data("append");

                nv=$.formatNum(nv);

                if(prepend && prepend.length>0){
                    nv=prepend+nv;
                    $(this).val(nv);
                }
                if(append && append.length>0){
                    nv=nv+append;
                    $(this).val(nv);
                }
            });
        };
        $.init=function(){
            $("INPUT").focus(function(){
                console.log("Focused");
                let $t=$(this);
                let nv=$t.val().replace(/[^\d\\.]+/,"");
                console.log("Val Old",$t.val());
                console.log("Val New",nv);
                $t.val(nv);
            });
            $("INPUT").blur(function(){
                $.calculate($(this));
            });

            $.calculate($("#margin"));
        };
        $.init();
    });
</script>
@endsection
