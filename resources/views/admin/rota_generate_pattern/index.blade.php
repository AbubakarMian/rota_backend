@extends('layouts.default_module')
@section('module_name')
 Rota Generate pattern
@stop
@section('add_btn')

{!! Form::open(['method' => 'get', 'route' => ['special.rota.create'], 'files'=>true]) !!}
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
{{-- {!! Form::open(['method' => 'get', 'route' => ['doctor.search'], 'files'=>true]) !!}
@include('admin.doctor.partial.searchfilters')
{!!Form::close() !!}
@stop --}}

<thead>
	<tr>

        <th>Total days  of month </th>


	</tr>
</thead>
<tbody>



	<tr>
<td>
        for ($i = 1; $i <= 28; $i++) {}



</td>















	</tr>
    @endforeach
    {{-- @endforeach --}}
</tbody>
@section('pagination')
{{-- <span class="pagination pagination-md pull-right">{!! $general->render() !!}</span> --}}
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
