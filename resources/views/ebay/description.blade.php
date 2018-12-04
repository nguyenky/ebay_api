<!DOCTYPE html>
<html lang='en'>
<head>
    <title>{{ @$item["Name"] }}</title>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Anton'>
    <style>
        html{
            box-sizing:border-box;
            -moz-box-sizing:border-box;
            -webkit-box-sizing:border-box
        }
        body{
            font-size:100%;
            font-family:'Helvetica',Arial,sans-serif;
            -webkit-font-smoothing:antialiased;
            margin:0px
        }
        *{
            margin:0;
            padding:0;
            outline:none;
            border:none;
            text-decoration:none
        }
        .product{
            margin-bottom:50px
        }
        .wrap-title{
            padding:50px
        }
        .product-detail{
            margin-top:50px
        }
        p{
            margin-top:20px;
            margin-bottom:0;
            padding:0;
            font-size:14px;
            line-height:1.5
        }
        p strong{
            font-size:14px;
            font-weight:600
        }
        ul{
            font-size:14px
        }
        .tabs{
            display:flex;
            flex-wrap:wrap
        }
        .input{
            position:absolute;
            opacity:0
        }
        .label{
            background-color:#fff;
            float:left;
            border:none;
            outline:none;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            transition:0.3s;
            font-size:13px;
            color:#00005e;
            text-align:center;
            text-transform:uppercase;
            font-weight:bold;
            box-sizing:border-box;
            border-top:1px solid #d9d9d9;
            border-right:1px solid #d9d9d9;
            border-bottom:1px solid #d9d9d9;
            position:relative;
            top:1px;
            height:50px
        }
        .label.tab1{
            width:20%;
            border-left:1px solid #d9d9d9
        }
        .label.tab2{
            width:20%
        }
        .input:checked+.label{
            color:#D8A95B;
            position:relative;
            z-index:1;
            top:1px;
            border-bottom:1px solid #fff
        }
        @media (min-width: 600px){
            .label{
                width:auto
            }
        }
        .panel{
            display:none;
            background:#fff
        }
        @media (min-width: 600px){
            .panel{
                order:99
            }
        }
        .input:checked+.label+.panel{
            display:block
        }
        .tab{
            overflow:hidden;
            background-color:#fff;
            position:relative;
            bottom:-5px;
            display:inline-block
        }
        .tabcontent{
            border:1px solid #d9d9d9;
            padding:35px
        }
        .tabcontent *{
            -webkit-animation:scale 0.7s ease-in-out;
            -moz-animation:scale 0.7s ease-in-out;
            animation:scale 0.7s ease-in-out
        }
        @keyframes scale{
            0%{
                transform:scale(0.9);
                opacity:0
            }
            50%{
                transform:scale(1.01);
                opacity:0.5
            }
            100%{
                transform:scale(1);
                opacity:1
            }
        }
        .tabcontent ul{
            list-style-type:disc;
            letter-spacing:0.3px;
            line-height:26px;
            font-size:14px;
            padding-left:30px;
            margin-top:15px
        }
        .clear{
            clear:both
        }
        img{
            max-width:100%;
            display:block
        }
        .container{
            max-width:1150px;
            width:100%;
            margin:0 auto;
            padding-left:15px;
            padding-right:15px;
            background:#fff;
            color:#5f5f5f;
            font-size:14px;
            box-sizing:border-box
        }
        .gallery{
            width:60%;
            float:left;
            margin-top:24px;
            position:relative
        }
        .images-box{
            width:100%;
            max-width:482px;
            max-height:392px;
            height:392px;
            float:right
        }
        .defaultimg{
            position:absolute;
            top:10px;
            right:0;
            bottom:0;
            left:84px;
            width:100%;
            max-width:504px;
            height:392px;
            -webkit-animation:cssAnimation 0.8s 1 ease-in-out;
            -moz-animation:cssAnimation 0.8s 1 ease-in-out;
            -o-animation:cssAnimation 0.8s 1 ease-in-out;
            padding-left:46px;
            box-sizing:border-box
        }
        .defaultimg img{
            margin:0 auto;
            max-width:244px;
            width:100%
        }
        .small-images{
            list-style:none;
            float:left;
            margin-top:-19px
        }
        .small-images li{
            display:block;
            width:84px;
            height:90px;
            cursor:pointer
        }
        .small-images .item-content{
            position:relative;
            float:left;
            width:84px;
            height:105px;
            border-bottom:1px solid #d9d9d9
        }
        .small-images li:last-child .item-content{
            border-bottom:0px none;
        }
        .small-images .small-image{
            position:absolute;
            top:0;
            right:0;
            bottom:0;
            left:0;
            margin:auto;
            max-width:100%;
            max-height:100%
        }
        .small-images .gallery-content{
            position:absolute;
            top:0;
            left:84px;
            width:100%;
            max-width:504px;
            height:392px;
            display:none;
            padding-left:46px;
            box-sizing:border-box
        }
        .item-wrapper{
            width:100%;
            height:100%;
            position:relative
        }
        .small-images .gallery-content img{
            margin:0 auto;
            width:auto;
            max-height:100%
        }
        .small-images li.image:hover .gallery-content#image{
            display:block;
            -webkit-animation:cssAnimation 0.7661s 1 ease-in-out;
            -moz-animation:cssAnimation 0.7661s 1 ease-in-out;
            -o-animation:cssAnimation 0.7661s 1 ease-in-out;
            padding-top:5%
        }
        .small-images li:hover~.defaultimg{
            display:none
        }
        @-webkit-keyframes cssAnimation{
            from{
                -webkit-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)
            }
            to{
                -webkit-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)
            }
        }
        @-moz-keyframes cssAnimation{
            from{
                -moz-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)
            }
            to{
                -moz-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)
            }
        }
        @-o-keyframes cssAnimation{
            from{
                -o-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)
            }
            to{
                -o-transform:rotate(0deg) scale(1.0) skew(-180deg) translate(0px)
            }
        }
        .gallery-detail{
            width:40%;
            float:right;
            padding-top:10px;
            box-sizing:border-box
        }
        .gallery-detail h1{
            font-size:33px;
            font-weight:400;
            line-height:33px;
            color:#5f5f5f;
            letter-spacing:0.9px
        }
        .gallery-detail .price{
            font-size:22px;
            font-weight:600;
            color:#5f5f5f;
            margin-top:17px;
            letter-spacing:0.6px
        }
    </style>
</head>
<body>
<div class='container'>
    <div class='content'>
        <section class='product'>
            <div class='gallery-content'>
                @if(@$item && $images)
                <div class='gallery'>
                    <div class='images-box'></div>
                    <ul class='small-images' id='list-thumnail'>
                        @foreach($images as $img)
                        <li class='image'>
                            <div class='item-content'> <img class='small-image' src='{{$img}}'></div>
                            <div class='gallery-content' id='image'>
                                <div class='item-wrapper'> <img src='{{$img}}'></div>
                            </div>
                        </li>
                        @endforeach
                        <div class='defaultimg'>
                            <div class='inner'> <img src='{{$images[0]}}'></div>
                        </div>
                    </ul>
                    <div class='clear'></div>
                </div>
                @endif
                <div class='gallery-detail'>
                    <h1>{{ @$item["name"] }}</h1>
                    <p class='price'>{{"$".number_format(@$item["listing_price"],2)}}</p>
                </div>
                <div class='clear'></div>
            </div>
        </section>
        <section class='product-details'>
            <div class='tabs'>
                <input class='input' name='tabs' type='radio' id='tab-1' checked='checked'/> <label class='label tab1' for='tab-1'>Product details</label>
                <div class='panel'>
                    <div id='Product-details' class='tabcontent'>
                        {!! @$item->description !!}
                        <p></p>
                        <strong>Warranty:</strong>
                        <ul>
                            <li>Product Warranty: Statutory Warranty</li>
                        </ul>
                        <p></p>
                    </div>
                </div>
                <input class='input' name='tabs' type='radio' id='tab-2'/> <label class='label tab2' for='tab-2'>Shipping & Returns</label>
                <div class='panel'>
                    <div id='Shipping-returns' class='tabcontent'>
                        <p> 30 day returns<br><br> We want you to love the products you buy from us. If you change your mind, you may return it to us within 30 days of the date you received it, no questions asked. You will be responsible for all shipping charges to facilitate a change of mind return. If you change your mind, we will provide you with a refund in an amount equal to the price you paid for the product, less all shipping costs. Items returned must be in 'as-new' condition. This means you have not used, assembled, damaged, washed or laundered any of the items. Please return items secured in their original packaging. If you cannot return an item 'as new' in its original packaging, a handling and restocking fee may apply up to 20% of the value of the item. <br><br> Non-returnable items excluded from all change of mind returns:</p>
                        <ul>
                            <li>Products described as 'made to order'</li>
                            <li>Mattresses, bedding and pillows</li>
                            <li>Clearance items</li>
                            <li>Personalised items</li>
                        </ul>
                        <br>
                        <p>Within 5 business days of receiving your return, and subject to confirming it is in 'as-new' condition, we will issue you with a refund via email in an amount equal to the price you paid for the product, less the cost to ship the product to you and the return shipping back to the warehouse. The return shipping cost is the same as the initial delivery fee. If you purchase an item with promotional shipping (discounted or free shipping) and you return it because you change your mind, we will deduct the actual shipping costs from your refund. Both the cost of shipping the item to you and the cost of the return shipping to the warehouse will be deducted. We will not accept returns delivered in person to our offices or warehouse facilities.</p>
                        <br><br>
                        <p>Refunds by law: In Australia, consumers have a legal right to obtain a refund from a business for goods purchased if the goods are faulty, not fit for purpose or don't match description. More information at returns.<br></p>
                        <p></p>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <p>&nbsp;</p>
</div>
</body>
</html>
