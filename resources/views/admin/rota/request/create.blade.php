

@extends('layouts.default_edit')
{{-- @section('heading')
    {!! $heading !!}
@endsection --}}

@section('leftsideform')


        {!! Form::open(['id'=>'my_form','method' => 'POST', 'route' => ['request.save' ], 'files'=>true]) !!}

       @include('admin.rota.request.partial.form')
        {!!Form::close()!!}


    <div class="col-md-5 pull-left">
        <div class="form-group text-center">
            <div>
                {!! Form::open(['method' => 'get', 'route' => ['rota.doctor.index']]) !!}

                {!! Form::close() !!}
            </div>
        </div>
    </div>

@endsection
{!!Form::close()!!}




