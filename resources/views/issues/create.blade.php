@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2>Добавление задачи в остлеживаемые</h2>
        <form>
            <div class="form-row">
                <div class="form-group col-1">
                    <label for="issue_id"># Задачи</label>
                    <input type="text" class="form-control" id="issue_id" value="{{$issue_id}}">
                </div>
                <div class="form-group col-4">
                    <label for="title">Название</label>
                    <input type="text" class="form-control" id="title" placeholder="Иванов Иван Васильевич: Зависает ПК">
                </div>
                <div class="form-group col-2">
                    <label for="category">Категория</label>
                    <select class="form-control" id="category">
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                    </select>
                </div>
                <div class="form-group col-1">
                    <label for="estimated_hours">Расчетное время</label>
                    <input type="number" class="form-control" id="estimated_hours">
                </div>
                <div class="form-group col-2">
                    <label for="created_on">Дата создания</label>
                    <input type="datetime-local" class="form-control" id="created_on">
                </div>
                <div class="form-group col-auto">
                    <label>&nbsp;</label>
                    <button type="submit" class="form-control btn btn-primary">Добавить</button>
                </div>
            </div>
        </form>
    </div>
@endsection