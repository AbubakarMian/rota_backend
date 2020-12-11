@extends('layouts.default_module')
@section('module_name')
Rota Request
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

        <th>Name</th>
		<th>Age</th>
        <th>Qualification</th>
        <th>Total_duties </th>
		<th>Image</th>
        <th>Leave Page</th>
        <th>Rota Request</th>

	</tr>
</thead>
<tbody>

    @foreach($doctors as $d)

	<tr>
        <td>{!! $d->user->name !!}</td>
		<td>{!! $d->age!!}</td>
        <td>{!! $d->qualification !!}</td>
        <td>{!! $d->total_duties !!}</td>

		<?php
                if(!$d->user->avatar){
                    $d->user->avatar = asset('avatar/default_img.jpg');
                }
            ?>



        <td><img width="100px" src="{!! $d->user->avatar!!}" class="show-product-img"></td>
        <td>  <a href="{{ url('rota/leave/'.$d->id) }}">Leave</a></td>
        <td>  <a href="{{ url('rota/request/'.$d->id) }}">Rota Request</a></td>


        {{-- <td>{!! Form::open(['method' => 'POST', 'route' => ['rota.leave' , $d->id]]) !!}
			<a href=""  name="leave page" data-target=".leave">
				<span class="badge bg-info">
					{!! 'leave page' !!}</span></a>
			{!! Form::close() !!}
        </td> --}}

        {{-- <td>
            {{ route('rota/leave'.$d->id) }}
			{!! Form::close() !!}
		</td>
      </td> --}}

        {{-- <td>{!! Form::open(['method' => 'POST', 'route' => ['rota.leave', $d->id]]) !!}
			<a href="" name="rota" data-target=".delete">
				<span class="badge bg-info">
					{!! $d->deleted_at?'Activate':'Deactivate' !!}</span></a>
			{!! Form::close() !!}
        </td> --}}





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
