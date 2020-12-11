
    <div class="form-group">
        {!! Form::label('startdate','Start_date') !!}
        <div>
            {!! Form::date('startdate',null, ['class' => 'form-control',
            'data-parsley-required'=>'true',
            'data-parsley-trigger'=>'change',
            'placeholder'=>'startdate','required',
            'maxlength'=>"100"]) !!}
        </div>
    </div>
<input name="doctor_id" id="doctor_id" value="{{$doctor->id}}" hidden>
    <div class="form-group">
        {!! Form::label('enddate','End_date') !!}
        <div>
            {!! Form::date('enddate',null, ['class' => 'form-control',
            'data-parsley-required'=>'true',
            'data-parsley-trigger'=>'change',
            'placeholder'=>'enddate','required',
            'maxlength'=>"100"]) !!}
        </div>

    </div>


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
