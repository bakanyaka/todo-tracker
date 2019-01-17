<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Redmine URI -->
    <meta name="redmine-uri" content="{{ config('services.redmine.uri') }}">
    <!-- Styles -->
    <link href="{{ mix('css/vendor.css') }}" rel="stylesheet">
    <link href="{{ mix('css/style.css') }}" rel="stylesheet">
</head>

<!-- BODY options, add following classes to body to change options
// Header options
1. '.header-fixed'					- Fixed Header
// Brand options
1. '.brand-minimized'       - Minimized brand (Only symbol)
// Sidebar options
1. '.sidebar-fixed'					- Fixed Sidebar
2. '.sidebar-hidden'				- Hidden Sidebar
3. '.sidebar-off-canvas'		- Off Canvas Sidebar
4. '.sidebar-minimized'			- Minimized Sidebar (Only icons)
5. '.sidebar-compact'			  - Compact Sidebar
// Aside options
1. '.aside-menu-fixed'			- Fixed Aside Menu
2. '.aside-menu-hidden'			- Hidden Aside Menu
3. '.aside-menu-off-canvas'	- Off Canvas Aside Menu
// Breadcrumb options
1. '.breadcrumb-fixed'			- Fixed Breadcrumb
// Footer options
1. '.footer-fixed'					- Fixed footer
-->

<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">
@yield('content')
<!-- Pass Routes to JS -->
@routes
<!-- Pass variables to JS -->
@include('client-js-vars')
<!-- Scripts -->
<script src="{{ mix('js/manifest.js') }}"></script>
<script src="{{ mix('js/vendor.js') }}"></script>
<script src="{{ mix('js/main.js') }}"></script>
</body>
</html>