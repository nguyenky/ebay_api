<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->

    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

    <!-- Styles -->

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://blackrockdigital.github.io/startbootstrap-sb-admin-2/dist/css/sb-admin-2.css">
    <link rel="stylesheet" href="https://blackrockdigital.github.io/startbootstrap-sb-admin-2/vendor/datatables-plugins/dataTables.bootstrap.css">

    <!-- Optional theme -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous"> -->
    <style type="text/css">
        .mode-test form{
            display: flex;
            justify-content: space-around;
        }
        H4.modal-title,
        H5.modal-title{
            font-size: 23px;
            font-weight: 500;
        }
    </style>

    
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    @guest
                    @else
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item"><a class="nav-link" href="{{ url('/home') }}">Home</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dd-ebay-links" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">eBay Links</a>
                            <div class="dropdown-menu" aria-labelledby="dd-ebay-links">
                                <a class="dropdown-item" href="https://www.ebay.com.au/sh/lst/active" target="_blank">Active Listings</a>
                                <a class="dropdown-item" href="https://www.ebay.com.au/usr/ixplorestoreaus" target="_blank">Store (Public)</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{route("merchant-location")}}" target="_blank">Merchant Location</a>
                                <a class="dropdown-item" href="https://developer.ebay.com/my/api_test_tool?index=0&api=inventory&call=offer__GET&variation=json&env=production" target="_blank">API Test Tool</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dd-testing" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Reports</a>
                            <div class="dropdown-menu" aria-labelledby="dd-testing">
                                <a class="dropdown-item" href="#">Missing Images Report</a>
                                <a class="dropdown-item" href="#">Bad File Sync Report</a>
                                <a class="dropdown-item" href="#">Stale eBay Sync Report</a>
                                <a class="dropdown-item" href="#">Overpriced Report</a>
                                <a class="dropdown-item" href="#">Low Stock Report</a>
                            </div>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{route('manual-processing')}}">Manual Processing</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="dd-testing" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Testing</a>
                            <div class="dropdown-menu" aria-labelledby="dd-testing">
                                <a class="dropdown-item" href="{{route('begin')}}">Begin Process</a>
                                <a class="dropdown-item" href="{{route('refresh')}}">Refresh Token</a>
                                <a class="dropdown-item" href="{{route('getItem')}}">Get Item</a>
                                <a class="dropdown-item" href="{{route('mode-test')}}">Mode Test</a>
                            </div>
                        </li>
                    </ul>
                    @endguest

                    <div class="pull-right">
                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ml-auto text-right">
                            <!-- Authentication Links -->
                            @guest
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            <!--
                                <li class="nav-item">
                                    <a href="#" class="nav-link"><i class="fas fa-search"></i></a>
                                </li>
                            //-->
                            @endguest
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
<footer>
</footer>
</html>
