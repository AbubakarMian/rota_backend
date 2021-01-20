<div class="form-group">
    {!! Form::label('doctor') !!}

    <select id="select-example" class="form-control" name="doctor_id" placeholder="Select doctor...">
        @foreach ($doctor as $d)
        <option value="{{$d->id}}">{{$d->user->name}}</option>

        @endforeach
    </select>
</div>

<div class="form-group">
    {!! Form::label('dutydate','Duty
    Date') !!}
    <div>
        {!! Form::date('dutydate',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'dutydate','required',
        'maxlength'=>"100"]) !!}
    </div>
</div>



<div class="form-group">
    <div>
        <input type="radio" name="duty" value="wantduty" id="myRadio" onchange="toggle_element('shift');" checked>
        <span class="myduty" onclick="">Want duty</span>
        <input type="radio" name="duty" value="wantoff" onchange="toggle_element('shift');">
        <span class="myoff">Want off</span>
    </div>
</div>

<div>
    <div class="form-group" id="shift">
        {!! Form::label('shiftday','Shift') !!}
        <div>
            {!! Form::select('shiftday',$shifts,null, ['class' => 'form-control',
            'data-parsley-required'=>'true',
            'data-parsley-trigger'=>'change',
            'required']) !!}
        </div>
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

    function toggle_element(element){
        console.log('toggle_element',element);
        $("#"+element).toggle();
    }

</script>

@endsection
