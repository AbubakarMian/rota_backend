@extends('layouts.default_module')
@section('module_name')
Request Details
@stop
@section('add_btn')




@section('table-properties')
width="400px" style="table-layout:fixed;"
@endsection


<style>
    td {
        white-space: nowrap;
        overflow: hidden;
        width: 30px;
        height: 30px;
        text-overflow: ellipsis;
    }
</style>

@section('table')
{!! Form::open(['method' => 'get', 'route' => ['doctor.search'], 'files'=>true]) !!}
@include('admin.doctor.partial.searchfilters')
{!!Form::close() !!}
@stop
<thead>
    <tr>



        <th>Duty Date</th>
        <th>Weekday</th>

    </tr>
</thead>
<tbody>

    @foreach($request as $lv)


    <tr>
        <td>{!! $lv->duty_date !!}</td>
        <td>{!! $lv->week_day_id !!}</td>





















    </tr>


    @endforeach

</tbody>

@section('pagination')
<span class="pagination pagination-md pull-right">{!! $doctors->render() !!}</span>
<div class="col-md-3 pull-left">
    <div class="form-group text-center">
        <div>
            {!! Form::open(['method' => 'get', 'route' => ['dashboard']]) !!}
            {!! Form::submit('Cancel', ['class' => 'btn btn-default btn-block btn-lg btn-parsley']) !!}
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
@stop

