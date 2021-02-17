@extends('layouts.default_module')
@section('module_name')
Special Rota Request
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
{!! Form::open(['method' => 'get', 'route' => ['special.rota.search'], 'files'=>true]) !!}
@include('admin.special_rota_request.partial.searchfilters')
{!!Form::close() !!}
{{-- @stop --}}

<thead>
    <tr>
        <th>Doctor </th>
        <th>Duty Date </th>
        <th>Want Duty </th>
        <th>Want OFF </th>
        {{-- <th>Annual leave</th> --}}
        <th>Shift</th>
        <th>Delete</th>


    </tr>
</thead>
<tbody>
    {{-- <strong>{{ $message }}</strong> --}}

    @foreach($list as $g)

    <tr>
        <td>{!! $g->doctor->user->name!!}</td>

        <td>{!! date('d F, Y (l)', $g->duty_date)!!}</td>

        @if($g->want_duty == 1)
        <td>true</td>
        @else
        <td>false      </td>
        @endif

        @if($g->want_off==1)
        <td>true</td>
        @else
        <td>false</td>
        @endif

        <td>{!! $g->shift!!}</td>
        <td>{!! Form::open(['method' => 'POST', 'route' => ['special_rota_request.delete', $g->id]]) !!}
			<a href="" data-toggle="modal" name="activate_delete" data-target=".delete">
				<span class="badge bg-info btn-danger ">
					{!! $g->deleted_at?'Activate':'Delete' !!}</span></a>
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
