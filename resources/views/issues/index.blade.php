@extends('layouts.app')

@section('title','Остлеживаемые задачи')

@section('content')
    @include('issues.actions')
    <issues endpoint="{{route('api.issues')}}"></issues>
@endsection

