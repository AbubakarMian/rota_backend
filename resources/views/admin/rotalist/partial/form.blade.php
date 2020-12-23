<div class="form-group">
    {!! Form::label('year','Year') !!}
    <div>
        {!! Form::number('year',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'year','required',
        'min'=>"2020" , 'max'=>'2200']) !!}
    </div>

<div class="form-group">
    {!! Form::label('month','Month') !!}
</div>

    <select id="select-example" class="form-control"  name="month"  placeholder="Select month...">

        <option value="1">January</option>
        <option value="2">February</option>
        <option value="3">March</option>
        <option value="4">April</option>
        <option value="5">May</option>
        <option value="6">June</option>
        <option value="7">July</option>
        <option value="8">August</option>
        <option value="9">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>



    </select>













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
