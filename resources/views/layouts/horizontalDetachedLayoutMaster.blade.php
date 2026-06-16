<body class="horizontal-layout horizontal-menu {{$configData['horizontalMenuType']}} {{ $configData['blankPageClass'] }}
{{ $configData['bodyClass'] }}
{{ $configData['sidebarClass'] }} {{ $configData['verticalMenuNavbarType'] }} {{ $configData['footerType'] }}" data-menu="horizontal-menu" data-col="{{ $configData['showMenu'] === true ? '' : '1-column' }}" data-open="hover" data-layout="{{ ($configData['theme'] === 'light') ? '' : $configData['layoutTheme'] }}" style="{{ $configData['bodyStyle'] }}" data-framework="laravel" data-asset-path="{{ asset('/')}}">

  @if((isset($configData['showMenu']) && $configData['showMenu'] === true))
  @include('panels.sidebar')
  @endif

  @include('panels.navbar')
  @include('panels.horizontalMenu')
  <div class="app-content content {{ $configData['pageClass'] }}">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <div class="content-wrapper">
      @if($configData['pageHeader'] == true && isset($configData['pageHeader']))
      @include('panels.breadcrumb')
      @endif
      <div class="{{ $configData['sidebarPositionClass'] }}">
        <div class="sidebar">
          @yield('content-sidebar')
        </div>
      </div>
      <div class="{{ $configData['contentsidebarClass'] }}">
        <div id="app" class="content-body">
          @yield('content')
        </div>
      </div>
    </div>
  </div>

  @if($configData['blankPage'] == false)
  @include('content/pages/customizer')
  @endif

  <div class="sidenav-overlay"></div>
  <div class="drag-target"></div>
  @include('panels/footer')
  @include('panels/scripts')

  <script type="text/javascript">
    $(window).on('load', function() {
      if (feather) {
        feather.replace({
          width: 14,
          height: 14
        });
      }
    })
  </script>
</body>

</html>