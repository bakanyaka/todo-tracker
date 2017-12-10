@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <form class="mt-5"  method="POST" action="{{route('services.update', ['id' => $service->id])}}">
                    <div class="form-group">
                        <label for="name">Название сервиса:</label>
                        <input type="text" class="form-control" id="name" value="{{$service->name}}" name="name" placeholder="Например: Регистрация пользователя">
                    </div>
                    <div class="form-group">
                        <label for="hours">Количество часов:</label>
                        <input type="number" class="form-control" id="hours" value="{{$service->hours}}" name="hours" placeholder="Например: 5">
                    </div>
                    <button type="submit" class="btn btn-primary">Обновить</button>
                    {{csrf_field()}}
                    {{method_field('PATCH')}}
                </form>
            </div>
        </div>
    </div>
@endsection
