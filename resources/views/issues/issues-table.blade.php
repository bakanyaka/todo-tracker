
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <tr>
                <th>№</th>
                <th>Название</th>
                <th>Подразделение</th>
                <th>Назначена</th>
                <th>Приоритет</th>
                <th>Сервис</th>
                <th>Расчетное время</th>
                <th>Оставшееся время</th>
                <th>Дата создания</th>
                <th>Плановая дата завершения</th>
                <th>Фактическая дата завершения</th>
                <th>&nbsp;</th>
            </tr>
            @foreach($issues as $issue)
                <tr class="{{$issue->time_left < 0 ? 'table-danger' : ''}}">
                    <td><a href="{{config('services.redmine.uri') . '/issues/' . $issue->id}}">{{$issue->id}}</a></td>
                    <td>{{$issue->subject}}</td>
                    <td>{{$issue->department}}</td>
                    <td>{{$issue->assigned_to or ''}}</td>
                    <td>{{$issue->priority->name}}</td>
                    <td>{{$issue->service->name or 'Прочее'}}</td>
                    <td>{{$issue->service->hours or ''}}</td>
                    <td class="{{($issue->percent_of_time_left < 30 && $issue->percent_of_time_left !== null )? 'table-warning' : ''}}">
                        {{$issue->time_left or ''}}
                    </td>
                    <td>{{$issue->created_on}}</td>
                    <td>{{$issue->due_date or ''}}</td>
                    <td>{{$issue->closed_on or ''}}</td>
                    <td class="align-middle">
                        @if($issue->isTrackedBy(auth()->user()))
                        <a class="btn btn-sm btn-primary" href="#" onclick="event.preventDefault();document.getElementById('issue-{{$issue->id}}-untrack-form').submit();" role="button">Удалить</a>
                        <form id="issue-{{$issue->id}}-untrack-form" action="{{route('issues.untrack', ['id' => $issue->id])}}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                            {{method_field('DELETE')}}
                        </form>
                        @else
                            <a class="btn btn-sm btn-primary" href="#" onclick="event.preventDefault();document.getElementById('issue-{{$issue->id}}-track-form').submit();" role="button">Следить</a>
                            <form id="issue-{{$issue->id}}-track-form" action="{{route('issues.track', ['id' => $issue->id])}}" method="POST" style="display: none;">
                                <input class="form-control form-control-sm mr-sm-2" name="issue_id" type="text" value="{{$issue->id}}" placeholder="# Задачи">
                                {{ csrf_field() }}
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>