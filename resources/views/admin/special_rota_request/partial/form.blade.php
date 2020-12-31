
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


 <input type="radio" name="duty" value="wantduty"  id="myRadio" checked>Want duty &nbsp;
<input type="radio"  name="duty" value="wantoff">Want Off

{{-- <div class="form-group">

    <div>
        {!! Form::radio('duty',null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'enddate',
        'value'=>"annual_leave"]) !!} &nbsp;
        {!! Form::label('annual','Annual Leave') !!}
    </div>

</div> --}}


<div class="form-group" id="shift">
    {!! Form::label('shiftday','Shift') !!}
    <div>
        {!! Form::select('shiftday',$shifts,null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'required']) !!}
    </div>
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
