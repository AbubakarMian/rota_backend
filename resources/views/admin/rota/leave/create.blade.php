

@extends('layouts.default_edit')
@section('heading')
    Add {!! $doctor->user->name !!}'s Leave
@endsection

@section('leftsideform')
    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>{{ $message }}</strong>
        </div>
    @endif


        {!! Form::open(['id'=>'my_form','method' => 'POST', 'route' => ['leave.save' ], 'files'=>true]) !!}

       @include('admin.rota.leave.partial.form')
        {!!Form::close()!!}


    <div class="col-md-5 pull-left">
        <div class="form-group text-center">
            <div>
                {!! Form::open(['method' => 'get', 'route' => ['rota.doctor.index']]) !!}
                {!! Form::submit('Cancel', ['class' => 'btn btn-default btn-block btn-lg btn-parsley']) !!}
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@endsection
{!!Form::close()!!}




