@extends('layouts.default_module')
@section('module_name')
Doctor's Summary
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
        <th>Total duties </th>
        <th>Image</th>
        <th>Leave Details</th>
        <th>Rota Details</th>
        <th>Add Leaves</th>
        <th>Add Rota Request</th>

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

        <td>

            {{-- <a href="">
                <span class="badge bg-info"
                    data-url="{!! asset('admin/rota/leave/detailmodal/').'/'.$d->id !!}" name='msgmodal'>
                    Leave Details</span>
            </a> --}}
            <a href="{{ url('admin/rota/leave/detail/'.$d->id)  }}" class='badge bg-info'>    Leave Details
               </a>

        </td>

        <td> <a href="{{ url('admin/rota/request/detail/'.$d->id)  }}" class='badge bg-info'> Rota Request
                Details</a></td>
        {{-- <td>
			<a href="" data-toggle="modal" name="activate_delete" data-target=".request_{!! $q->id !!}">
				<span class=" badge bg-info btn-success">
                   Rota Request Details</span></a>
			@include('admin.rota_request.partial.request_modal',['order'=>$q])
		</td> --}}



        <td> <a href="{{ url('rota/leave/'.$d->id)  }}" class='badge bg-info'>Leave</a></td>
        <td> <a href="{{ url('rota/request/'.$d->id) }}" class='badge bg-info'>Rota Request</a></td>


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
    {{-- @endforeach
    @endforeach --}}
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

@include('admin.rota_request.partial.image_modal')
@section('app_jquery')
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>
    $(function(){

			$('span[name="msgmodal"]').on('click', function(e){
				e.preventDefault();
                console.log('span !!!!!!!!!!!')
				var my_url = $(this).attr('data-url');
				var mySpan = this;
				$.get(my_url , function (data) {
					var trHTML = '';
					$.ajax({
						type: 'GET',
						url: my_url,
						data: 'application/json',
						dataType: 'json',
						success: function (data) {

								console.log("sucess data !!!!!!!!!!!!",data);
								trHTML = data;

							$('#my_msg_div').html(trHTML);
							$('#msgmodal').modal('show');
						},
						error: function (data) {
							console.log('Error:', data);
						}
					});
				});
			});
		});

</script>
