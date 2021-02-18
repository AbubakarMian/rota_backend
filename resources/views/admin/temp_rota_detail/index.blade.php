@extends('layouts.default_module')
@section('module_name')
Temp Rota Duties Detail
@stop
@section('add_btn')

{{-- {!! Form::open(['method' => 'get', 'route' => ['doctor.create'], 'files'=>true]) !!}
<span>{!! Form::submit('Add', ['class' => 'btn btn-success pull-right']) !!}</span>
{!! Form::close() !!} --}}
@stop

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
{{-- {!! Form::open(['method' => 'get', 'route' => ['doctor.search'], 'files'=>true]) !!}
@include('admin.doctor.partial.searchfilters')
{!!Form::close() !!} --}}
{{-- @stop --}}

<thead>
	<tr>

        <th>Id</th>
        <th>Name</th>
        <th>Actual Total Duties </th>
        <th>Extra Request Duties </th>
        <th>Required Morning</th>
        <th>Required Evening</th>
        <th>Required Night</th>
		<th>Given Morning</th>
		<th>Given Evening</th>
		<th>Given Night</th>
		<th>Total Given Duties</th>
		<th>Given Leaves</th>



	</tr>
</thead>
<tbody>
    @foreach($rota_details as $rd)
    <?php
        $general_morning = isset($rd->doctor->general_rota_morning->total_duties)?$rd->doctor->general_rota_morning->total_duties:0;
        $general_evening = isset($rd->doctor->general_rota_evening->total_duties)?$rd->doctor->general_rota_evening->total_duties:0;
        $general_night = isset($rd->doctor->general_rota_night->total_duties)?$rd->doctor->general_rota_night->total_duties:0;
    ?>
	<tr>
        <td>{!! $rd->doctor_id!!}</td>
        <td>{!! $rd->doctor->user->name!!}</td>
        <td>{!! $rd->doctor->total_duties!!}</td>
        <td>{!! $rd->doctor->extra_duties!!}</td>
		<td>{!! $general_morning!!}</td>
		<td>{!! $general_evening!!}</td>
		<td>{!! $general_night!!}</td>
		<td>{!! $rd->total_morning !!}</td>
		<td>{!! $rd->total_evening !!}</td>
		<td>{!! $rd->total_night !!}</td>
		<td>{!! $rd->total_duties !!}</td>
		<td>{!! $rd->total_leaves !!}</td>

	</tr>
	@endforeach
</tbody>
@section('pagination')
<span class="pagination pagination-md pull-right">{!! $rota_details->render() !!}</span>
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
