@extends('layouts.default_module')
@section('module_name')
Temp Rota Details 
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

        <th>Doctor Id</th>
        <th>Doctor Name</th>
		<th>Total Morning</th>
		<th>Total Evening</th>
		<th>Total Night</th>
		<th>Total Duties</th>
		<th>Total Leaves</th>

        

	</tr>
</thead>
<tbody>

    @foreach($rota_details as $rd)

	<tr>
        <td>{!! $rd->doctor_id!!}</td>
        <td>{!! $rd->doctor->user->name!!}</td>
		<td>{!! $rd->total_morning!!}</td>
        <td>{!! $rd->total_evening!!}</td>
		<td>{!! $rd->total_night !!}</td>
		<td>{!! $rd->total_duties!!}</td>
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
