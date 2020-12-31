@extends('layouts.default_module')
@section('module_name')
Leave Details
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
{{-- @stop --}}
<thead>
    <tr>



        <th>Start Date</th>
        <th>End Date</th>

    </tr>
</thead>
<tbody>

    @foreach($leave_request as $dt)

{{-- ('d F, Y (l)'); --}}
    <tr>
        <td>{!! date('d F, Y (l)', $dt->start_date) !!}</td>
        <td>{!! date('d F, Y (l)', $dt->end_date )!!}</td>
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

