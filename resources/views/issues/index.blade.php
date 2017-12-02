@extends('layouts.app')

@section('content')
<table>
    <tr>
        <th>№</th>
        <th>Название</th>
        <th>Дата создания</th>
        <th>Плановая дата завершения</th>
    </tr>
    @foreach($issues as $issue)
        <tr>
            <td>{{$issue->issue_id}}</td>
            <td>{{$issue->title}}</td>
            <td>{{$issue->created_on}}</td>
        </tr>
    @endforeach
</table>
@endsection

