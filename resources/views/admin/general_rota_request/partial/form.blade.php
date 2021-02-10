
 <div class="form-group">
    {!! Form::label('total_duties','Total duties') !!}
    <div>
        {!! Form::number('total_duties',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'total duties','required',
        'maxlength'=>"100"]) !!}
    </div>
 </div>

    <div class="form-group">
        {!! Form::label('shift','Shift') !!}


        <select id="select-example" class="form-control"  name="shift"  placeholder="Select shift...">
            <option name="shift" value="morning">Morning</option>
            <option name="shift" value="evening">Evening</option>
            <option name="shift" value="night">Night</option>
        </select>


    </div>
<div class="form-group">
    {!! Form::label('doctor') !!}


  <select id="select-example" class="form-control"  name="doctor_id"  placeholder="Select doctor...">
    @foreach ($doctor as $d)
    <option value="{{$d->id}}">{{$d->user->name}}</option>

    @endforeach
</select>
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
