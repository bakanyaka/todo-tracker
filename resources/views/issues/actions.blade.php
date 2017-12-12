<section id="actions" class="py-4 mb-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <a href="{{route('issues', ['user' => 'all'])}}" class="btn btn-primary btn-block">Все отслеживаемые задачи</a>
            </div>
            <div class="col-md-3">
                <a href="{{route('issues', ['user' => 'all', 'only_open' => 'true'])}}" class="btn btn-primary btn-block">Открытые отслеживаемые задачи</a>
            </div>
            <div class="col-md-3">
                <a href="{{route('issues')}}" class="btn btn-primary btn-block">Отслеживаемые мной задачи</a>
            </div>
        </div>
    </div>
</section>