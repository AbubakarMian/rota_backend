
@extends('layouts.default_module')
@section('module_name')
leave page
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


<thead>
	<tr>

        <th>Star date</th>

        <th>end date </th>

	</tr>
</thead>
<tbody>

    @foreach($rota as $r)

	<tr>


        <td>{!! $r->star_date !!}</td>

        <td>{!! $r->_end_date !!}</td>










	</tr>
    @endforeach

</tbody>
@section('pagination')
<span class="pagination pagination-md pull-right">{!! $r->render() !!}</span>
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
