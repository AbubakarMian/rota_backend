@extends('layouts.default_module')
@section('module_name')
Leave Details
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
{{-- {!! Form::open(['method' => 'get', 'route' => ['doctor.search'], 'files'=>true]) !!}
@include('admin.doctor.partial.searchfilters')
{!!Form::close() !!} --}}
{{-- @stop --}}
<thead>
    <tr>
        {{-- @foreach($leave_request as $lr) --}}

<h3>
     " Doctor : {!! ucwords($doctors->user->name) !!}"
</h3>

        <th>Start Date</th>
        <th>End Date</th>
        <th>Annual</th>
        <th>Delete</th>

    </tr>
</thead>
<tbody>

    @foreach($leave_request as $lr)
    {{-- {!!dd($status)!!} --}}
    {{-- ('d F, Y (l)'); --}}
    <tr>
        <td>{!! date('d F, Y (l)', $lr->start_date) !!}</td>
        <td>{!! date('d F, Y (l)', $lr->end_date )!!}</td>
        <td>{!!$lr->annual_leave?'True':'False' !!}</td>
        {{-- <td id="status_{!!$lr->id!!}">
            @if($lr->status == 'pending')
            <span class=" badge bg-info "  data-toggle="modal" data-target=".accept_request_{!!$lr->id!!}">
                Accepted
            </span>
            @include('admin.rota_request.partial.confirmation_modal',['leave'=>$lr,'status'=>'accept','req_status'=>'accept_request_'.$lr->id])

            <span class="badge bg-info" data-toggle="modal" data-target=".reject_request_{!!$lr->id!!}">
                Reject
            </span>
            @include('admin.rota_request.partial.confirmation_modal',['leave'=>$lr,'status'=>'reject','req_status'=>'reject_request_'.$lr->id])
            @else
            {!! ucfirst($lr->status)!!}
            @endif
        </td> --}}


        <td>{!! Form::open(['method' => 'POST', 'route' => ['admin.leave.delete', $lr->id]]) !!}
			<a href="" data-toggle="modal" name="activate_delete" data-target=".delete">
				<span class="badge bg-info btn-danger ">
					{!! $lr->deleted_at?'Activate':'Delete' !!}</span></a>
			{!! Form::close() !!}
		</td>
    </tr>
    @endforeach

</tbody>

@section('pagination')
<span class="pagination pagination-md pull-right">{!! $leave_request->render() !!}</span>
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
@section('app_jquery')
<script>
    function update_status(id,status){

    $.ajax({

        url:'{!!asset("admin/rota/leave/status")!!}/'+id,
        method:'POST',
        dataType: 'json',
        data: {'status':status,
               '_token' :'{!! csrf_token() !!}'
              },
        success:function(data){
            console.log('respons data',data.status);
            if(data.status){
                console.log('respons status',data.status);
                $('#status_'+id).html(data.new_value);
            }
        },
        error:function (err){
            console.log('error us',err);
        }

    });

}


</script>
@endsection
@stop
