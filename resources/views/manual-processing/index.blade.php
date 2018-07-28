@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Manual Processing
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-danger text-center">
                            {{ session('status') }}
                        </div>
                    @endif
                        <div class="panel-group" id="accordion">
                            <div class="panel {{(in_array(session('manual-step'),['step1','']))?'panel-primary':'panel-default'}}" id="step1">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse1">Step 1: Download CSV</a></h4>
                                </div>
                                <div id="collapse1" class="panel-collapse collapse{{(in_array(session('manual-step'),['step1','']))?' in':''}}">
                                    <div class="panel-body">
                                        <p>It will run job <code>app\Jobs\dropbox\DownLoadCSV</code> at 00:00 o'clock to download csv file to an application Dropbox. You can also press the button below to execute manually.</p>
                                        <a href="{{route("manual-processing-step1")}}" data-step="1" class="btn btn-default runnable">Run</a>
                                    </div>
                                </div>
                            </div>

                            <div class="panel {{(session('manual-step')==='step2')?'panel-primary':'panel-default'}}" id="step2">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse2">Step 2: Check CSV</a></h4>
                                </div>
                                <div id="collapse2" class="panel-collapse collapse{{(session('manual-step')==='step2')?' in':''}}">
                                    <div class="panel-body">
                                        <p>It will run job <code>app\Jobs\dropbox\CheckCSVFile</code>. You can also press the button below to execute manually. In this job, It will check the change between the csv file and the saved product in the DB. Any item that has not been saved to the DB it will save, if It have a change it will run job <code>app\Jobs\dropbox\CheckCSVFile</code>.( In mode test, it check only the change ).</p>
                                        <a href="{{route("manual-processing-step2")}}" class="btn btn-default runnable">Run</a>
                                    </div>
                                </div>
                            </div>

                            <div class="panel {{(session('manual-step')==='step3')?'panel-primary':'panel-default'}}" id="step3">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse3">Step 3: Create Inventory on eBay</a></h4>
                                </div>
                                <div id="collapse3" class="panel-collapse collapse{{(session('manual-step')==='step3')?' in':''}}">
                                    <div class="panel-body">
                                        <p>It will run job <code>app\Jobs\ebay\CreateInventoryEbay</code> at 12:00 o'clock to create inventory ebay. You can also press the button below to execute manually. It will get the items in DB, if the item has "offerID" and empty "listingID", it will create inventory on eBay.</p>
                                        <a href="#" class="btn btn-default">Run</a>
                                    </div>
                                </div>
                            </div>

                            <div class="panel {{(session('manual-step')==='step4')?'panel-primary':'panel-default'}}" id="step4">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse4">Step 4: Create Offer on eBay</a></h4>
                                </div>
                                <div id="collapse4" class="panel-collapse collapse{{(session('manual-step')==='step4')?' in':''}}">
                                    <div class="panel-body">
                                        <p>It will run job <code>app\Jobs\ebay\CreateOfferEbay</code> at 0:00 o'clock to create offer ebay. You can also press the button below to execute manually.</p>
                                        <a href="#" class="btn btn-default">Run</a>
                                    </div>
                                </div>
                            </div>

                            <div class="panel {{(session('manual-step')==='step5')?'panel-primary':'panel-default'}}" id="step5">
                                <div class="panel-heading">
                                    <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse5">Step 5: Make offer public on eBay</a></h4>
                                </div>
                                <div id="collapse5" class="panel-collapse collapse{{(session('manual-step')==='step5')?' in':''}}">
                                    <div class="panel-body">
                                        <p>It will run job <code>app\Jobs\ebay\PublicOfferEbay</code> at 0:00 o'clock to create offer ebay. You can also press the button below to execute manually. If item empty "listingID", it will listing item on eBay.</p>
                                        <a href="#" class="btn btn-default">Run</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="application/javascript">
    $(function(){
        $("#accordion H4 A").click(function(){
            var topParent=$(this).parent().parent().parent();
            $("#accordion .collapse.in,#accordion .collapse.show").removeClass("in").removeClass("show");
            //$(".collapse",topParent).toggleClass("show");

            $("#accordion .panel-primary").each(function(){
                $(this).removeClass("panel-primary").addClass("panel-default");
            });

            if(topParent.hasClass("panel-primary")){
                topParent.removeClass("panel-primary");
            }else{
                topParent.removeClass("panel-default").addClass("panel-primary");
            }
        });

        $(".runnable").click(function(){
            var p=$(this).parent();
            var t=$(this);
            t.text("Loading...");
            var frame=$("<iframe></iframe>").attr("src",$(this).attr("href")).attr("style","display: block;margin: 20px auto 0px auto;width: 100%;border:1px solid #cccccc;").on("load", function() {
                t.text("Run again");
                var step=t.data("step")+1;
                $("#step"+step+" H4 A").click();
            });
            $("IFRAME",p).remove();
            p.append(frame);
            return(false);
        });
    });
</script>
@endsection
