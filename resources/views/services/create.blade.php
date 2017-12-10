@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <form class="mt-5" method="POST" action="{{route('services')}}">
                    <div class="form-group">
                        <label for="name">Название сервиса:</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Например: Регистрация пользователя">
                    </div>
                    <div class="form-group">
                        <label for="hours">Количество часов:</label>
                        <input type="number" class="form-control" id="hours" name="hours" placeholder="Например: 5">
                    </div>
                    <button type="submit" class="btn btn-primary">Создать</button>
                    {{csrf_field()}}
                </form>
            </div>
        </div>
    </div>
@endsection
