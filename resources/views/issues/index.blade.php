@extends('layouts.app')

@section('title','Остлеживаемые задачи')

@section('content')
    @include('issues.actions')
    @include('issues.issues-table')
@endsection

