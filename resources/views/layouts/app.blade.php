<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand font-sm-28 font-md-36" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt fa-fw"></i> ログインする</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus fa-fw"></i> 新しくお客様情報を登録する</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}様としてログインしています
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    
                                    <a class="dropdown-item" href="{{ route('users.show', Auth::user()) }}"><i class="far fa-user-circle fa-fw"></i> お客様情報を確認する</a>
                                    <a class="dropdown-item" href="{{ route('user_licenses.edit') }}"><i class="fas fa-id-card fa-fw"></i> ご登録の本人確認書類を編集する</a>
                                    <a class="dropdown-item" href="{{ route('user_pays.edit') }}"><i class="far fa-credit-card fa-fw"></i> ご登録のお支払い方法を編集する</a>
                                    <a class="dropdown-item" href="{{ route('user_papers.edit') }}"><i class="far fa-file fa-fw"></i> ご登録の補助書類を編集する</a>
                                    <a class="dropdown-item" href="{{ route('memos.create') }}"><i class="far fa-check-square fa-fw"></i>お手続きに必要な書類を確認する</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                                     <i class="fas fa-sign-out-alt fa-fw"></i> 
                                        ログアウトする
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    
                                </div>


                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
        <div class="container-fluid">
            <div class="row alert alert-secondary mb-0">
                <div class="col-12 col-sm-8 d-flex align-items-center">お問い合わせは<br class="d-inline d-sm-none">インフォメーションセンターへ</div>
                <div class="col-12 col-sm-4 col-rows-1 col-rows-xl-2">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <i class="fas fa-phone fa-fw"></i><span class="d-none d-md-inline">お問い合わせ</span>
                        </div>
                        <div>0120-xxx-xxx</div>
                    </div>
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <i class="far fa-clock fa-fw"></i><span class="d-none d-md-inline">営業時間</span>
                        </div>
                        <div>xx:00~xx:00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
