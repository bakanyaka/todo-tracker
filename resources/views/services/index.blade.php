@extends('layouts.app')

@section('title','Сервисы')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 mb-2">
                    <a class="btn btn-primary pull-right" href="{{route('services.new')}}" role="button">Cоздать</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <tr>
                            <th>Название</th>
                            <th>Часов</th>
                            <th>&nbsp;</th>
                        </tr>
                        @foreach($services as $service)
                            <tr>
                                <td>{{$service->name}}</td>
                                <td>{{$service->hours}}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{route('services.edit', ['id' => $service->id])}}" role="button">Редактировать</a>
                                    <a class="btn btn-sm btn-primary" href="#" onclick="event.preventDefault();document.getElementById('service-{{$service->id}}-delete-form').submit();" role="button">Удалить</a>
                                    <form id="service-{{$service->id}}-delete-form" action="{{route('services.delete', ['id' => $service->id])}}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                        {{method_field('DELETE')}}
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
