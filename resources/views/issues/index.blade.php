@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="table-responsive">
        <table class="table table-striped">
            <tr>
                <th>№</th>
                <th>Название</th>
                <th>Дата создания</th>
                <th>Категория</th>
                <th>Расчетное время</th>
                <th>Плановая дата завершения</th>
                <th>Фактическая дата завершения</th>
            </tr>
            @foreach($issues as $issue)
                <tr>
                    <td>{{$issue->issue_id}}</td>
                    <td>{{$issue->title}}</td>
                    <td>{{$issue->created_on}}</td>
                    <td></td>
                    <td></td>
                    <td>{{$issue->due_date}}</td>
                    <td></td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection

