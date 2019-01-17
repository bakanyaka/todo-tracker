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
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card-group mt-5">
                    <div class="card p-4">
                        <div class="card-body">
                            <h1>Вход</h1>
                            <p class="text-muted">Введите данные вашей учетной записи</p>
                            <form method="POST" action="{{ route('login') }}">
                                {{ csrf_field() }}
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="icon-user"></i></span>
                                    <input title="username" placeholder="Имя пользователя" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus >
                                </div>
                                @if ($errors->has('username'))
                                    <div class="invalid-feedback d-flex">
                                        {{ $errors->first('username') }}
                                    </div>
                                @endif
                                <div class="input-group mt-3">
                                    <span class="input-group-addon"><i class="icon-lock"></i></span>
                                    <input type="password" placeholder="Пароль" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                </div>
                                @if ($errors->has('password'))
                                    <div class="invalid-feedback d-flex">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </div>
                                @endif
                                <div class="form-check mt-3">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input"  name="remember" {{ old('remember') ? 'checked' : '' }}> Запомнить меня
                                    </label>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary px-4">Войти</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>