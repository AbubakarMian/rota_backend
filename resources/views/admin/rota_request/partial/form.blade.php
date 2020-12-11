<div class="form-group">
    {!! Form::label('name','Name') !!}
    <div>
        {!! Form::text('name',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'name','required',
        'maxlength'=>"100"]) !!}
    </div>


<div class="form-group">
    {!! Form::label('email','Email') !!}
    <div>
        {!! Form::text('email',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'email','required',
        'maxlength'=>"100"]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('age','Age') !!}
        <div>
            {!! Form::number('age',null, ['class' => 'form-control',
            'data-parsley-required'=>'true',
            'data-parsley-trigger'=>'change',
            'placeholder'=>'age','required',
            'maxlength'=>"100"]) !!}
        </div>

</div>
<div class="form-group">
    {!! Form::label('qualification','Qualification') !!}
    <div>
        {!! Form::text('qualification',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'qualification','required']) !!}
    </div>

</div>

<div class="form-group">
    {!! Form::label('total_duties','Total_duties') !!}
    <div>
        {!! Form::number('total_duties',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'total_duties','required']) !!}
    </div>

</div>

<div class="form-group">
    {!! Form::label('id','Id') !!}
    <div>
        {!! Form::number('id',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'id','required']) !!}
    </div>

</div>





<?php

$avatar =  asset('avatar/default_img.jpg');

if(isset($user)){

    if($user->avatar){
        $avatar = $user->avatar;
    }
}
?>

<div class="form-group">

    <div class="form-group pull-right">
        <img width="100px" src="{!! $avatar !!}" class="show-product-img" data-toggle="modal" data-target=".imagemodal">
    </div>

    <div class="form-group">
        {!! Form::label('avatar','Image') !!}
        {!! Form::file('avatar', ['class' => 'choose-image', 'id'=>'avatar'] ) !!}
        <p class="help-block" id="error">Limit 2MB</p>
    </div>

</div>
@include('admin.doctor.partial.image_modal')

<span id="err" class="error-product"></span>


<div class="form-group col-md-12">
</div>





<div class="col-md-5 pull-left">
    <div class="form-group text-center">
        <div>
            {!!Form::submit('Save',
            ['class'=>'btn btn-primary btn-block btn-lg btn-parsley','onblur'=>'return validateForm();'])!!}
        </div>
    </div>
</div>

@section('app_jquery')
<script>




    function validateForm(){
        return true;
    }

</script>

@endsection
