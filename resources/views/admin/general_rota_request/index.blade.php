@extends('layouts.default_module')
@section('module_name')
General Rota Request
@stop
@section('add_btn')

{!! Form::open(['method' => 'get', 'route' => ['general.rota.create'], 'files'=>true]) !!}
<span>{!! Form::submit('Add', ['class' => 'btn btn-success pull-right']) !!}</span>
{!! Form::close() !!}

@endsection
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
{!! Form::open(['method' => 'get', 'route' => ['general.rota.search'], 'files'=>true]) !!}
@include('admin.general_rota_request.partial.searchfilters')
{!!Form::close() !!}
{{-- @stop --}}

<thead>
	<tr>

        <th>Doctor </th>
        <th>Shift </th>
        <th>Total Duties</th>
        <th>Delete</th>


	</tr>
</thead>
<tbody>

    @foreach($list as $gh)
    {{-- @foreach($doctor as $g) --}}
	<tr>

        <td>{!! $gh->doctor->user->name!!}</td>

        <td>{!! ucfirst($gh->shift) !!}</td>
        <td>{!! $gh->total_duties!!}</td>

        <td>{!! Form::open(['method' => 'POST', 'route' => ['admin.general_rota_request.delete', $gh->id]]) !!}
			<a href="" data-toggle="modal" name="activate_delete" data-target=".delete">
				<span class="badge bg-info btn-danger ">
					{!! $gh->deleted_at?'Activate':'Delete' !!}</span></a>
			{!! Form::close() !!}
		</td>
	</tr>
    @endforeach
    {{-- @endforeach --}}
</tbody>
@section('pagination')
<span class="pagination pagination-md pull-right">{!! $list->render() !!}</span>
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
