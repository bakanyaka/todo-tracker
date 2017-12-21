@extends('layouts.app')

@section('title','Остлеживаемые задачи')

@section('content')
    @include('issues.actions')
    <div class="container-fluid">
        <div>
            Последняя синхронизация с Redmine была {{$lastSync}}
        </div>
        @include('issues.issues-table')
        <nav aria-label="Pagination">
            {{ $issues->appends(Request::input())->links() }}
        </nav>
    </div>
@endsection

