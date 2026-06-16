@if ($configData['mainLayoutType'] === 'horizontal' && isset($configData['mainLayoutType']))
    <nav class="header-navbar navbar-expand-lg navbar navbar-fixed align-items-center navbar-shadow navbar-brand-center {{ $configData['navbarColor'] }}"
        data-nav="brand-center">
        <div class="navbar-header d-xl-block d-none">
            <ul class="nav navbar-nav">
                <li class="nav-item">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <span class="brand-logo">
                            <svg viewbox="0 0 139 95" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" height="24">
                                <defs>
                                    <lineargradient id="linearGradient-1" x1="100%" y1="10.5120544%" x2="50%"
                                        y2="89.4879456%">
                                        <stop stop-color="#000000" offset="0%"></stop>
                                        <stop stop-color="#FFFFFF" offset="100%"></stop>
                                    </lineargradient>
                                    <lineargradient id="linearGradient-2" x1="64.0437835%" y1="46.3276743%"
                                        x2="37.373316%" y2="100%">
                                        <stop stop-color="#EEEEEE" stop-opacity="0" offset="0%"></stop>
                                        <stop stop-color="#FFFFFF" offset="100%"></stop>
                                    </lineargradient>
                                </defs>
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Artboard" transform="translate(-400.000000, -178.000000)">
                                        <g id="Group" transform="translate(400.000000, 178.000000)">
                                            <path class="text-primary" id="Path"
                                                d="M-5.68434189e-14,2.84217094e-14 L39.1816085,2.84217094e-14 L69.3453773,32.2519224 L101.428699,2.84217094e-14 L138.784583,2.84217094e-14 L138.784199,29.8015838 C137.958931,37.3510206 135.784352,42.5567762 132.260463,45.4188507 C128.736573,48.2809251 112.33867,64.5239941 83.0667527,94.1480575 L56.2750821,94.1480575 L6.71554594,44.4188507 C2.46876683,39.9813776 0.345377275,35.1089553 0.345377275,29.8015838 C0.345377275,24.4942122 0.230251516,14.560351 -5.68434189e-14,2.84217094e-14 Z"
                                                style="fill:currentColor"></path>
                                            <path id="Path1"
                                                d="M69.3453773,32.2519224 L101.428699,1.42108547e-14 L138.784583,1.42108547e-14 L138.784199,29.8015838 C137.958931,37.3510206 135.784352,42.5567762 132.260463,45.4188507 C128.736573,48.2809251 112.33867,64.5239941 83.0667527,94.1480575 L56.2750821,94.1480575 L32.8435758,70.5039241 L69.3453773,32.2519224 Z"
                                                fill="url(#linearGradient-1)" opacity="0.2"></path>
                                            <polygon id="Path-2" fill="#000000" opacity="0.049999997"
                                                points="69.3922914 32.4202615 32.8435758 70.5039241 54.0490008 16.1851325">
                                            </polygon>
                                            <polygon id="Path-21" fill="#000000" opacity="0.099999994"
                                                points="69.3922914 32.4202615 32.8435758 70.5039241 58.3683556 20.7402338">
                                            </polygon>
                                            <polygon id="Path-3" fill="url(#linearGradient-2)" opacity="0.099999994"
                                                points="101.428699 0 83.0667527 94.1480575 130.378721 47.0740288">
                                            </polygon>
                                        </g>
                                    </g>
                                </g>
                            </svg></span>
                        <h2 class="brand-text mb-0">Promolider</h2>
                    </a>
                </li>
            </ul>
        </div>
    @else
        <nav
            class="header-navbar navbar navbar-expand-lg align-items-center {{ $configData['navbarClass'] }} navbar-light navbar-shadow {{ $configData['navbarColor'] }}">
@endif
<div class="navbar-container d-flex content">
    <div class="bookmark-wrapper d-flex align-items-center">
        <ul class="nav navbar-nav d-xl-none">
            <li class="nav-item"><a class="nav-link menu-toggle" href="javascript:void(0);"><i class="ficon"
                        data-feather="menu"></i></a></li>
        </ul>
        {{-- <ul class="nav navbar-nav bookmark-icons">
            <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ url('app/email') }}"
                    data-toggle="tooltip" data-placement="top" title="Email"><i class="ficon"
                        data-feather="mail"></i></a></li>
            <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ url('app/chat') }}"
                    data-toggle="tooltip" data-placement="top" title="Chat"><i class="ficon"
                        data-feather="message-square"></i></a></li>
            <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ url('app/calendar') }}"
                    data-toggle="tooltip" data-placement="top" title="Calendar"><i class="ficon"
                        data-feather="calendar"></i></a></li>
            <li class="nav-item d-none d-lg-block"><a class="nav-link" href="{{ url('app/todo') }}"
                    data-toggle="tooltip" data-placement="top" title="Todo"><i class="ficon"
                        data-feather="check-square"></i></a></li>
        </ul> --}}
        {{-- <ul class="nav navbar-nav">
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link bookmark-star">
                    <i class="ficon text-warning" data-feather="star"></i>
                </a>
                <div class="bookmark-input search-input">
                    <div class="bookmark-input-icon">
                        <i data-feather="search"></i>
                    </div>
                    <input class="form-control input" type="text" placeholder="Bookmark" tabindex="0"
                        data-search="search">
                    <ul class="search-list search-list-bookmark"></ul>
                </div>
            </li>
        </ul> --}}
    </div>
    <ul class="nav navbar-nav align-items-center ml-auto">
        {{-- <li class="nav-item dropdown dropdown-language">
            <a class="nav-link dropdown-toggle" id="dropdown-flag" href="javascript:void(0);" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="flag-icon flag-icon-us"></i>
                <span class="selected-language">English</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-flag">
                <a class="dropdown-item" href="{{ url('lang/en') }}" data-language="en">
                    <i class="flag-icon flag-icon-us"></i> English
                </a>
                <a class="dropdown-item" href="{{ url('lang/fr') }}" data-language="fr">
                    <i class="flag-icon flag-icon-fr"></i> French
                </a>
                <a class="dropdown-item" href="{{ url('lang/de') }}" data-language="de">
                    <i class="flag-icon flag-icon-de"></i> German
                </a>
                <a class="dropdown-item" href="{{ url('lang/pt') }}" data-language="pt">
                    <i class="flag-icon flag-icon-pt"></i> Portuguese
                </a>
            </div>
        </li> --}}
        {{-- <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-style"><i class="ficon"
                    data-feather="{{ $configData['theme'] === 'dark' ? 'sun' : 'moon' }}"></i></a></li> --}}
        {{-- <li class="nav-item nav-search"><a class="nav-link nav-link-search"><i class="ficon"
                    data-feather="search"></i></a>
            <div class="search-input">
                <div class="search-input-icon"><i data-feather="search"></i></div>
                <input class="form-control input" type="text" placeholder="Explore Promolider..." tabindex="-1"
                    data-search="search">
                <div class="search-input-close"><i data-feather="x"></i></div>
                <ul class="search-list search-list-main"></ul>
            </div>
        </li> --}}
        @php
            $credits = auth()->user()->credits ?? 0;
        @endphp

        <li class="nav-item dropdown dropdown-cart mr-25">
            <a class="nav-link" href="javascript:void(0);" data-toggle="dropdown">

                {{-- CUADRADO VERDE CON CRÉDITOS --}}
                <div style="
                    width:34px;
                    height:34px;
                    background:#28a745;
                    border-radius:8px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    color:white;
                    font-weight:bold;
                    font-size:14px;
                    box-shadow:0px 2px 6px rgba(0,0,0,0.25);
                ">
                    {{ intval($credits) }}
                </div>

            </a>

            {{-- DROPDOWN DETALLADO --}}
            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">

                <li class="dropdown-menu-header">
                    <div class="dropdown-header d-flex">
                        <h4 class="notification-title mx-auto text-center">Mis Créditos</h4>
                    </div>
                </li>

                <li class="scrollable-container media-list">

                    <div class="media d-flex align-items-center py-1 px-1">
                        <div class="media-left pr-1">
                            <img src="https://cdn-icons-png.flaticon.com/512/625/625288.png"
                                 width="40" height="40">
                        </div>

                        <div class="media-body">
                            <h4 class="font-weight-bolder mb-0">{{ number_format($credits, 2, '.', ',') }}</h4>
                            <small class="text-muted">Créditos disponibles</small>
                        </div>
                    </div>

                </li>

                <li class="dropdown-menu-footer">
                    <a class="btn btn-success btn-block" href="{{ url('credits/history') }}">
                        Ver Historial
                    </a>
                </li>

            </ul>
        </li>
        @inject('level', 'App\Services\UserLevelService')
        @inject('rank', 'App\Services\RankService')
        @php
            $nextLevel = $level->nextLevel();
            $porce = $level->porcentaje();
            $rank = $rank->myRank();
        @endphp
        <li class="nav-item dropdown dropdown-cart mr-25"><a class="nav-link" href="javascript:void(0);"
                data-toggle="dropdown">
                <i class="ficon"> <img src="{{ $rank->icon }}" alt="logo_nivel" width="25px" height="25px">
                </i>
            </a>
            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                    <div class="dropdown-header d-flex">
                        <h4 class="notification-title mx-auto text-center">Ultimo Rango Obtenido</h4>
                    </div>

                    <div class="d-flex justify-content-center mb-2">
                        <a href="#">

                            <div class="row">

                            </div>
                            <div class="row">

                                <div>
                                    <div class="col-12 d-flex justify-content-center">
                                        <img class="" src="{{ $rank->icon }}" alt="logo_nivel" width="150px"
                                            height="150px">
                                    </div>
                                    <div class="col-12 text-center mb-2 text-uppercase font-weight-bold">
                                        {{ $rank->name }}
                                    </div>
                                </div>

                                <div class="mt-1">

                                    <div class="col-12 font-weight-bold text-center">
                                        <h2>Nv. {{ $rank->id }}</h2>
                                    </div>

                                    <div class="col-12">
                                        <p>Volumen mínimo : {{ $rank->vol_min }}</p>
                                    </div>

                                    <div class="col-12">
                                        <p>Pack máximo : {{ $rank->pack_max }}</p>
                                    </div>

                                    <div class="col-12">
                                        <p>Directos activos : {{ $rank->active_direct }}</p>
                                    </div>

                                    <div class="col-12">
                                        <p>Pago máximo : {{ $rank->max_pay }}</p>
                                    </div>

                                </div>

                            </div>
                        </a>
                    </div>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown dropdown-cart mr-25"><a class="nav-link" href="javascript:void(0);"
                data-toggle="dropdown">
                <i class="ficon"> <img
                        src="https://static.vecteezy.com/system/resources/previews/028/585/658/non_2x/apple-3d-rendering-icon-illustration-free-png.png"
                        alt="logo_nivel" width="25px" height="25px"> </i>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped" style="width:{{ $porce }}%">
                    </div>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                    <div class="dropdown-header d-flex">
                        <h4 class="notification-title mx-auto text-center">Mis Puntos</h4>
                    </div>
                    <div class="d-flex justify-content-center mb-2">
                        <a href="{{ url('badges/my-badges') }}">
                            <div class="row">
                                <div class="col-12 d-flex justify-content-center">
                                    <img class="" src="{{ asset('images/logo/logo_nivel.png') }}" alt="logo_nivel"
                                        width="50px" height="50px">
                                </div>
                                <div class="col-12 text-center">
                                    {{ $myLevel = $nextLevel->description }}
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        {{-- <div class="progress-bar" style="width:70%">70%</div> --}}
                                        <div class="progress-bar" style="width:{{ $porce }}%">
                                            {{ number_format((float)$porce, 0) }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </li>
                <li class="scrollable-container media-list">
                    @foreach ($level->myPointsDetail() as $item)
                        <div class="media align-items-center"><img class="d-block rounded mr-1"
                                src="https://cdn-icons-png.flaticon.com/512/4291/4291373.png" alt="donuts"
                                width="30">
                            <div class="media-body row">
                                <div class="col-6">
                                    <h6 class="cart-item-title text-capitalize">{{ $item->description }}</h6>
                                </div>
                                <div class="col-6">
                                    <h5 class="">{{ $item->increment_points }} Puntos</h5>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </li>
                <li class="dropdown-menu-footer">
                    <div class="d-flex justify-content-between mb-1">
                        <h6 class="font-weight-bolder mb-0">Total:</h6>
                        <h6 class="text-primary font-weight-bolder mb-0">{{ $level->myPoints() }}
                            Puntos</h6>
                    </div>
                    {{-- <a class="btn btn-primary btn-block" href="{{ url('app/ecommerce/checkout') }}">Checkout</a> --}}
                </li>
            </ul>
        </li>
        @inject('simplon', 'App\Services\NotificationService')

        <li class="nav-item dropdown dropdown-notification mr-25"><a class="nav-link"
                onclick="updateNotifications('{{ url('setting/update-notifications') }}');"
                href="javascript:void(0);" data-toggle="dropdown"><i class="ficon" data-feather="bell"></i>
                @if ($simplon->countNotification() > 0)
                    <span class="badge badge-pill badge-primary badge-up">{{ $simplon->countNotification() }}</span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                    <div class="dropdown-header d-flex">
                        <h4 class="notification-title mb-0 mr-auto">Notificaciones</h4>
                        {{-- <div class="badge badge-pill badge-light-primary">{{ $simplon->countNotification() }} New
                        </div> --}}
                    </div>
                </li>

                <li class="scrollable-container media-list"><a class="d-flex" href="javascript:void(0)">
                        <div class="media d-flex align-items-center">
                            <h6 class="font-weight-bolder mx-2 mb-0">Nuevas</h6>
                            <div class="badge badge-pill badge-light-primary">{{ $simplon->countNotification() }}
                            </div>
                        </div>
                        @foreach ($simplon->myNotification() as $dt)
                            <a class="d-flex" href="javascript:void(0)">
                                <div class="media d-flex align-items-start">
                                    <div class="media-left">
                                        <div class="avatar"><img src="{{ $dt->photo }}" alt="avatar"
                                                width="32" height="32"></div>
                                    </div>
                                    <div class="media-body">
                                        <p class="media-heading"><span
                                                class="font-weight-bolder">{{ $dt->title }}</span>&nbsp;
                                        </p><small class="notification-text">
                                            {{ $dt->body }}
                                        </small>
                                    </div>
                                    <div class="align-content-center">
                                        <button class="btn"><i class="ficon" data-feather="check"></i>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        @endforeach

                        <div class="media d-flex align-items-center">
                            <h6 class="font-weight-bolder mr-auto mb-0">Anteriores</h6>

                        </div>
                        @foreach ($simplon->notificationSeen() as $dt)
                            <a for="seen-notification" class="d-flex" href="javascript:void(0)">
                                <div class="media d-flex align-items-star">
                                    <div class="media-left">
                                        <div class="avatar"><img src="{{ $dt->photo }}" alt="avatar"
                                                width="32" height="32"></div>
                                    </div>
                                    <div class="media-body">
                                        <p class="media-heading"><span
                                                class="font-weight-bolder">{{ $dt->title }}</span>&nbsp;
                                        </p><small class="notification-text">
                                            {{ $dt->body }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                </li>
                <li class="dropdown-menu-footer"><a class="btn btn-primary btn-block"
                        href="{{ url('notifications') }}">Ver
                        todo</a></li>
            </ul>
        </li>
        <li class="nav-item dropdown dropdown-user">
            <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="javascript:void(0);"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="user-nav d-sm-flex d-none">
                    <span class="user-name font-weight-bolder">{{ e($get_auth_config['user']->name) }}
                        {{ e($get_auth_config['user']->last_name) }}</span>
                    <span class="user-status">{{ $get_auth_config['account_type']->account }}</span>
                    <input type="hidden" id="user_name_notify" value="{{ e($get_auth_config['user']->name) }}">
                </div>
                <span class="avatar">
                    <img class="round" src="{{ auth()->user()->photo }}" alt="avatar" height="40"
                        width="40">
                    <span class="avatar-status-online"></span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-user">
                <a class="dropdown-item" href="{{ url('setting/profile') }}">
                    <i class="mr-50" data-feather="user"></i> {{ __('locale.profile') }}
                </a>
                <a class="dropdown-item" href="{{ url('badges/my-badges') }}">
                    <i class="mr-50" data-feather="award"></i> Logros
                </a>
                {{-- <a class="dropdown-item" href="{{ url('app/email') }}">
                    <i class="mr-50" data-feather="mail"></i> {{ __('locale.inbox') }}
                </a> --}}
                {{-- <a class="dropdown-item" href="{{ url('app/todo') }}">
                    <i class="mr-50" data-feather="check-square"></i> Task
                </a> --}}
                {{-- <a class="dropdown-item" href="{{ url('app/chat') }}">
                    <i class="mr-50" data-feather="message-square"></i> Chats
                </a> --}}
                <a class="dropdown-item" href="{{ url('config/settings') }}">
                    <i class="mr-50" data-feather="settings"></i> Ajustes
                </a>
                <div class="dropdown-divider"></div>
                {{-- <a class="dropdown-item" href="{{ url('page/accounft-settings') }}">
                    <i class="mr-50" data-feather="settings"></i> Settings
                </a>
                <a class="dropdown-item" href="{{ url('page/pricing') }}">
                    <i class="mr-50" data-feather="credit-card"></i> Pricing
                </a>
                <a class="dropdown-item" href="{{ url('page/faq') }}">
                    <i class="mr-50" data-feather="help-circle"></i> FAQ
                </a> --}}
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <a class="dropdown-item" href="#" onclick="this.closest('form').submit();">
                        <i class="mr-50" data-feather="power"></i> {{ __('locale.logout') }}
                    </a>
                </form>
            </div>
        </li>
    </ul>
</div>
</nav>

{{-- Search Start Here --}}
<ul class="main-search-list-defaultlist d-none">
    <li class="d-flex align-items-center">
        <a href="javascript:void(0);">
            <h6 class="section-label mt-75 mb-0">Files</h6>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between w-100" href="{{ url('app/file-manager') }}">
            <div class="d-flex">
                <div class="mr-75">
                    <img src="{{ asset('images/icons/xls.png') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Two new item submitted</p>
                    <small class="text-muted">Marketing Manager</small>
                </div>
            </div>
            <small class="search-data-size mr-50 text-muted">&apos;17kb</small>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between w-100" href="{{ url('app/file-manager') }}">
            <div class="d-flex">
                <div class="mr-75">
                    <img src="{{ asset('images/icons/jpg.png') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">52 JPG file Generated</p>
                    <small class="text-muted">FontEnd Developer</small>
                </div>
            </div>
            <small class="search-data-size mr-50 text-muted">&apos;11kb</small>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between w-100" href="{{ url('app/file-manager') }}">
            <div class="d-flex">
                <div class="mr-75">
                    <img src="{{ asset('images/icons/pdf.png') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">25 PDF File Uploaded</p>
                    <small class="text-muted">Digital Marketing Manager</small>
                </div>
            </div>
            <small class="search-data-size mr-50 text-muted">&apos;150kb</small>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between w-100" href="{{ url('app/file-manager') }}">
            <div class="d-flex">
                <div class="mr-75">
                    <img src="{{ asset('images/icons/doc.png') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Anna_Strong.doc</p>
                    <small class="text-muted">Web Designer</small>
                </div>
            </div>
            <small class="search-data-size mr-50 text-muted">&apos;256kb</small>
        </a>
    </li>
    <li class="d-flex align-items-center">
        <a href="javascript:void(0);">
            <h6 class="section-label mt-75 mb-0">Members</h6>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between py-50 w-100" href="{{ url('app/user/view') }}">
            <div class="d-flex align-items-center">
                <div class="avatar mr-75">
                    <img src="{{ asset('images/portrait/small/avatar-s-8.jpg') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">{{ $get_auth_config['user']->name }}</p>
                    <small class="text-muted">UI designer</small>
                </div>
            </div>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between py-50 w-100" href="{{ url('app/user/view') }}">
            <div class="d-flex align-items-center">
                <div class="avatar mr-75">
                    <img src="{{ asset('images/portrait/small/avatar-s-1.jpg') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Michal Clark</p>
                    <small class="text-muted">FontEnd Developer</small>
                </div>
            </div>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between py-50 w-100" href="{{ url('app/user/view') }}">
            <div class="d-flex align-items-center">
                <div class="avatar mr-75">
                    <img src="{{ asset('images/portrait/small/avatar-s-14.jpg') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Milena Gibson</p>
                    <small class="text-muted">Digital Marketing Manager</small>
                </div>
            </div>
        </a>
    </li>
    <li class="auto-suggestion">
        <a class="d-flex align-items-center justify-content-between py-50 w-100" href="{{ url('app/user/view') }}">
            <div class="d-flex align-items-center">
                <div class="avatar mr-75">
                    <img src="{{ asset('images/portrait/small/avatar-s-6.jpg') }}" alt="png" height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Anna Strong</p>
                    <small class="text-muted">Web Designer</small>
                </div>
            </div>
        </a>
    </li>
</ul>

{{-- if main search not found! --}}
<ul class="main-search-list-defaultlist-other-list d-none">
    <li class="auto-suggestion justify-content-between">
        <a class="d-flex align-items-center justify-content-between w-100 py-50">
            <div class="d-flex justify-content-start">
                <span class="mr-75" data-feather="alert-circle"></span>
                <span>No results found.</span>
            </div>
        </a>
    </li>
</ul>
{{-- Search Ends --}}
<!-- END: Header-->

<script>
    function updateNotifications(url) {
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
            .catch(error => console.error('Error:', error))
            .then(response => {});
    }

    @if (session()->has('debug_binarycut'))
        console.log("{{ session('debug_binarycut') }}");
    @endif
    @if (session()->has('debug_user_rank'))
        console.log("{{ session('debug_user_rank') }}");
    @endif
    @if (session()->has('debug_wallet'))
        console.log("{{ session('debug_wallet') }}");
    @endif
    @if (session()->has('debug_rankBonusPay'))
        console.log("{{ session('debug_rankBonusPay') }}");
    @endif
    @if (session()->has('debug_binarycut_end'))
        console.log("{{ session('debug_binarycut_end') }}");
    @endif

    @if (session()->has('debug_rankBonus'))
        console.log("{{ session('debug_rankBonus') }}");
    @endif
    @if (session()->has('debug_rankFound'))
        console.log("{{ session('debug_rankFound') }}");
    @endif
    @if (session()->has('debug_noRankFound'))
        console.warn("{{ session('debug_noRankFound') }}");
    @endif
    @if (session()->has('debug_universityCount'))
        console.log("{{ session('debug_universityCount') }}");
    @endif
    @if (session()->has('debug_universityCondition'))
        console.log("{{ session('debug_universityCondition') }}");
    @endif
    @if (session()->has('debug_finalRank'))
        console.log("{{ session('debug_finalRank') }}");
    @endif

    @if (session()->has('debug_altRank'))
        console.log("{{ session('debug_altRank') }}");
    @endif

    @if (session()->has('debug_defaultRank'))
        console.warn("{{ session('debug_defaultRank') }}");
    @endif
    @if (session()->has('debug_rank'))
        console.log("{{ session('debug_rank') }}");
    @endif
</script>
