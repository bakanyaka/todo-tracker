<table>
    <tr>
        <th>№</th>
        <th>Название</th>
    </tr>
    @foreach($issues as $issue)
        <tr>
            <td>{{$issue->issue_id}}</td>
            <td>
                {{$issue->title}}
            </td>
        </tr>
    @endforeach
</table>

