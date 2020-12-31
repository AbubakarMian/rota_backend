<div class="form-group">
    {{-- {!! dd($doctor->user->name) !!} --}}
    {!! Form::label('name','Name') !!}
    <div>
        {!! Form::text('name', $doctor->user->name ?? null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'Name','required',
        'maxlength'=>"100"]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('email','Email') !!}
        <div>
            {!! Form::text('email',$doctor->user->email ?? null, ['class' => 'form-control',
            'data-parsley-required'=>'true',
            'data-parsley-trigger'=>'change',
            'placeholder'=>'Email','required',
            'maxlength'=>"100"]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('age','Age') !!}
            <div>
                {!! Form::number('age',null, ['class' => 'form-control',
                'data-parsley-required'=>'true',
                'data-parsley-trigger'=>'change',
                'placeholder'=>'Age','required',
                'maxlength'=>"100"]) !!}
            </div>

        </div>
        <div class="form-group">
            {!! Form::label('qualification','Qualification') !!}
            <div>
                {!! Form::text('qualification',null, ['class' => 'form-control',
                'data-parsley-required'=>'true',
                'data-parsley-trigger'=>'change',
                'placeholder'=>'Qualification','required']) !!}
            </div>

        </div>

        <div class="form-group">
            {!! Form::label('doctor_type','Doctor Type',) !!}
            {!! Form::select('doctor_type_id',$types,null,["placeholder"=>"Select
            Type","class"=>"form-control","required"]) !!}
            </select>
        </div>


        <div class="form-group">
            {!! Form::label('total_duties','Total Duties') !!}
            <div>
                {!! Form::number('total_duties',null, ['class' => 'form-control',
                'data-parsley-required'=>'true',
                'data-parsley-trigger'=>'change',
                'placeholder'=>'Total Duties','required']) !!}
            </div>

        </div>

        <?php

$avatar =  asset('images/medicallogo.png');

if(isset($user)){

    if($user->avatar){
        $avatar = $user->avatar;
    }
}
?>

        <div class="form-group">

            <div class="form-group pull-right">
                <img width="100px" src="{!! $avatar !!}"class="show-product-img imgshow">
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
