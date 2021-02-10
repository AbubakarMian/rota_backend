@extends('layouts.default_module')
@section('module_name')
Temporary Rota
@stop
@section('add_btn')
{!! Form::open(['method' => 'post', 'route' => ['temprota.new.generate',$monthly_rota_id], 'files'=>true]) !!}
<span>{!! Form::submit('New', ['class' => 'btn btn-success pull-right']) !!}</span>
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

        <th>Demo Number</th>
        <th>View</th>


    </tr>
</thead>
<tbody>

    @foreach($temp_rota as $t)

    <tr>
        <td>Demo-{!! $t->demo_num!!}</td>
        <td>
            <a href="{{ asset('admin/rota/view/'.$t->id) }}" class="badge bg-info" target="_blank">View </a>
        </td>

    </tr>
    @endforeach
</tbody>
@section('pagination')
<span class="pagination pagination-md pull-right">{!! $temp_rota->render() !!}</span>
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
