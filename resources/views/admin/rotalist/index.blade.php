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
        <th>Rota</th>
        <th> Monthly Rota Details</th>


	</tr>
</thead>
<tbody>

    @foreach($list as $l)

	<tr>
        <td>{!! $l->year!!}</td>
        <td>{!! date("F", mktime(0, 0, 0, $l->month, 10))!!}</td>
        <td>
            <a href="{{ asset('admin/rota/generate/'.$l->id) }}" class="badge bg-info">doctor name calender </a>
        </td>



        <td>
            {{-- @include('admin.rotalist.partial.calender') --}}
            <a href="{{ asset('admin/rota/generate/pattern/'.$l->id) }}" class="badge bg-info">  Rota Generate Pattern </a>
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
