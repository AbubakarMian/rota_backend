@extends('layouts.default_module')
@section('module_name')
Doctor
@stop
@section('add_btn')

{!! Form::open(['method' => 'get', 'route' => ['doctor.create'], 'files'=>true]) !!}
<span>{!! Form::submit('Add', ['class' => 'btn btn-success pull-right']) !!}</span>
{!! Form::close() !!}
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
{!! Form::open(['method' => 'get', 'route' => ['doctor.search'], 'files'=>true]) !!}
@include('admin.doctor.partial.searchfilters')
{!!Form::close() !!}
{{-- @stop --}}

<thead>
	<tr>

        <th>Name</th>
		<th>Age</th>
        <th>Qualification</th>
        <th>Total duties </th>
        <th>Doctor type</th>
		<th>Image</th>

        <th>Edit</th>
        <th>Delete</th>

	</tr>
</thead>
<tbody>

    @foreach($doctors as $d)

	<tr>

        <td>{!! $d->user->name !!}</td>
		<td>{!! $d->age!!}</td>
        <td>{!! $d->qualification !!}</td>
        <td>{!! $d->total_duties !!}</td>
        <td>{!! ucfirst($d->doctor_type->name) !!}</td>


		<?php
                if(!$d->user->avatar){
                    $d->user->avatar = asset('avatar/default_img.jpg');
                }
            ?>



		<td><img width="100px" src="{!! $d->user->avatar!!}" class="show-product-img imgshow"></td>


        <td>
			{!! link_to_action('Admin\DoctorController@edit',
			'Edit', array($d->id), array('class' => 'badge bg-info')) !!}

        </td>

        <td>{!! Form::open(['method' => 'POST', 'route' => ['doctor.delete', $d->id]]) !!}
			<a href="" data-toggle="modal" name="activate_delete" data-target=".delete">
				<span class="badge bg-info">
					{!! $d->deleted_at?'Activate':'Deactivate' !!}</span></a>
			{!! Form::close() !!}
		</td>
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
