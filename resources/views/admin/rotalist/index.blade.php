@extends('layouts.default_module')
@section('module_name')
Doctor Rota List
@stop
@section('add_btn')

{!! Form::open(['method' => 'get', 'route' => ['doctor.list.create'], 'files'=>true]) !!}
<span>{!! Form::submit('Add', ['class' => 'btn btn-success pull-right']) !!}</span>
{!! Form::close() !!}


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
@stop

<thead>
	<tr>
        <th>Year</th>
        <th>Month</th>
        <th>Temp Rota</th>
        <th>Rota</th>
        <th> Rota Generate Pattern</th>
        <th> Delete</th>
	</tr>
</thead>
<tbody>

    @foreach($list as $l)

	<tr>
        <td>{!! $l->year!!}</td>
        <td>{!! date("F", mktime(0, 0, 0, $l->month, 10))!!}</td>
        <td>
            <a href="{{ asset('admin/temp_rota/list/'.$l->id) }}" class="badge bg-info">view </a>
        </td>
        <td> @if($l->rota)
            <a href="{{ asset('admin/rota/calender/'.$l->id) }}" class="badge bg-info">view </a>
            @endif
        </td>
        <td>
            <a href="{{ asset('admin/rota/generate/pattern/'.$l->id) }}" class="badge bg-info">Edit </a>
        </td>
        <td>
            <a href="" hit_method="post" hit_url="{!!asset('admin/rota/delete/'.$l->id)!!}"
                data-toggle="modal" name="activate_delete_link" data-target=".delete"
                modal_heading="Alert" modal_msg="Do you want to proceed?">
				<span class="badge bg-info btn-danger ">
					Delete</span></a>
		</td>
   </tr>
	@endforeach
</tbody>
@section('pagination')
{{-- <span class="pagination pagination-md pull-right">{!! $l->render() !!}</span> --}}
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
