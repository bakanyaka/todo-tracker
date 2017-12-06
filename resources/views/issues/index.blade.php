@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <tr>
                <th>№</th>
                <th>Название</th>
                <th>Дата создания</th>
                <th>Сервис</th>
                <th>Расчетное время</th>
                <th>Плановая дата завершения</th>
                <th>Фактическая дата завершения</th>
            </tr>
            @foreach($issues as $issue)
                <tr>
                    <td>{{$issue->id}}</td>
                    <td>{{$issue->subject}}</td>
                    <td>{{$issue->created_on}}</td>
                    <td>{{$issue->service->name or 'Прочее'}}</td>
                    <td>{{$issue->service->hours or ''}}</td>
                    <td>{{$issue->due_date or 'Отсутствует'}}</td>
                    <td>{{$issue->closed_on or ''}}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection

