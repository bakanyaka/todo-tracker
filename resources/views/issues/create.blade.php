@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>Добавление задачи в остлеживаемые</h2>
        <form method="POST" action="{{route('issues.store')}}">
            <div class="form-row">
                <div class="form-group col-1">
                    <label for="issue_id"># Задачи</label>
                    <input type="text" class="form-control" id="issue_id" name="issue_id" value="{{$issue['id']}}" readonly>
                </div>
                <div class="form-group col-4">
                    <label for="subject">Название</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="{{$issue['subject']}}" readonly>
                </div>
                <div class="form-group col-2">
                    <label for="category">Сервис</label>
                    <select class="form-control" id="category" name="category">
                        <option>Приём и регистрация заявки</option>
                        <option>Регистрация пользователя в информационных системах</option>
                        <option>Выдача доступа к ресурсам ЛВС</option>
                        <option>Организация рабочих мест пользователей</option>
                        <option>Изменение конфигурации оборудования или программного обеспечения пользователей</option>
                    </select>
                </div>
                <div class="form-group col-1">
                    <label for="estimated_hours">Расчетное время</label>
                    <input type="number" class="form-control" id="estimated_hours" name="estimated_hours">
                </div>
                <div class="form-group col-2">
                    <label for="created_on">Дата создания</label>
                    <input type="text" class="form-control" id="created_on" name="created_on" value="{{$issue['created_on']}}" readonly>
                </div>
                <div class="form-group col-auto">
                    <label>&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Добавить</button>
                </div>
            </div>
            {{ csrf_field() }}
        </form>
    </div>
@endsection