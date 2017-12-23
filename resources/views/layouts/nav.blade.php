<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="{{ url('/') }}">
        {{ config('app.name', 'Laravel') }}
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="{{route('issues')}}">Задачи</a>
            </li>
{{--            <li class="nav-item">
                <a class="nav-link" href="{{route('services')}}">Сервисы</a>
            </li>--}}
        </ul>
        <form class="form-inline ml-5" method="POST" action="{{route('issues.track')}}">
            <input class="form-control form-control-sm mr-sm-2" name="issue_id" type="text" placeholder="# Задачи">
            <button class="btn btn-primary btn-sm my-2 my-sm-0" type="submit">Отслеживать</button>
            {{ csrf_field() }}
        </form>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="btn btn-primary btn-sm ml-sm-2" href="{{route('issues.update')}}">Обновить данные</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <div>
                <b-dropdown id="ddown1" text="{{ Auth::user()->name }}" variant="primary" size="sm" class="nav-link">
                    <b-dropdown-item>First Action</b-dropdown-item>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-item>Something else here...</b-dropdown-item>
                    <b-dropdown-item disabled>Disabled action</b-dropdown-item>
                    <a class="dropdown-item" href="{{ route('logout') }}"
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </b-dropdown>
            </div>
        </ul>
    </div>
</nav>