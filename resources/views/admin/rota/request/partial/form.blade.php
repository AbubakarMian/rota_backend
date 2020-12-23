<div class="form-group">
    {{-- <div> --}}
    <input type="checkbox" name="is_general" value="general" onclick="toggle_form(this,'duty_date');">
    {!! Form::label('general','Is General') !!}
    {{-- </div> --}}
</div>
<div class="form-group" id="duty_date">
    {!! Form::label('dutydate','Duty Date') !!}
    <div>
        {!! Form::date('dutydate',null, ['class' => 'form-control',
        'data-parsley-required'=>'true','id'=>'duty_date',
        'data-parsley-trigger'=>'change',
        'placeholder'=>'dutydate','required']) !!}
    </div>
</div>

{{-- <div class="form-group">
    <input type="checkbox" name="specific_week" value="specific_week" checked
        onclick="toggle_form(this,'specific_week_day');">
    {!! Form::label('specific_week','Select Week Day') !!}
</div>


<div class="form-group" id="specific_week_day">
    {!! Form::label('weekday_id','Week Day') !!}
    <div>
        {!! Form::select('weekday_id',$weekdays,null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change'
        ]) !!}
    </div>
</div> --}}


{{-- <input name="doctor_id" id="doctor_id" value="{{$doctor->id}}" hidden> --}}

<div class="form-group">
    {{-- <div> --}}
    <input type="checkbox" name="on_shift" value="1" checked onclick="toggle_form(this,'shift');">
    {!! Form::label('shift','Select Shift') !!}
    {{-- </div> --}}
</div>

<div class="form-group" id="shift">
    {!! Form::label('shiftday','Shift') !!}
    <div>
        {!! Form::select('shiftday',$shifts,null, ['class' => 'form-control',
        'data-parsley-required'=>'true',
        'data-parsley-trigger'=>'change',
        'required']) !!}
    </div>
</div>



{{-- <input type="radio" name="duty" value="wantduty">Want duty<br> --}}
{{-- <input type="radio"  name="duty" value="wantoff">Want Off<br> --}}



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
    function toggle_form(e,div) {

    $('#'+div).toggle('show');
}


    function validateForm(){
        return true;
    }

//     $(function() {
//   return $('#select-example').selectize();
// });



// function check() {
//   document.getElementById("red").checked = true;
// }
// function uncheck() {
//   document.getElementById("red").checked = false;
// }


// var button1 = document.getElementById("red");
// var button2 = document.getElementById("red");

// if (button1.checked){
//     alert("radio1 selected");
// }else if (button2.checked) {
//     alert("radio2 selected");
// }





</script>

@endsection
