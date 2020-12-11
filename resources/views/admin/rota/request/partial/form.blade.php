
    <div class="form-group">
        {!! Form::label('dutydate','Duty_date') !!}
        <div>
            {!! Form::date('dutydate',null, ['class' => 'form-control',
            'data-parsley-required'=>'true',
            'data-parsley-trigger'=>'change',
            'placeholder'=>'dutydate','required',
            'maxlength'=>"100"]) !!}
        </div>
    </div>


      <select id="select-example" class="form-control"  name="weekday_id"  placeholder="Select weekday...">
        @foreach ($weekday as $w)
        <option value="{{$w->id}}">{{$w->name}}</option>

        @endforeach
    </select>


<input name="doctor_id" id="doctor_id" value="{{$doctor->id}}" hidden>


    <input type="radio" name="shiftday" value="general">General<br>
    <input type="radio" name="shiftday" value="morning">Morning<br>
    <input type="radio" name="shiftday" value="night"> Night<br>
    <input type="radio" name="shiftday" value="cc">CC<br>

   <input type="radio" name="duty" value="wantduty">Want duty<br>
  <input type="radio"  name="duty" value="wantoff">Want Off<br>







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

//     $(function() {
//   return $('#select-example').selectize();
// });



// function check() {
//   document.getElementById("red").checked = true;
// }
// function uncheck() {
//   document.getElementById("red").checked = false;
// }


var button1 = document.getElementById("red");
var button2 = document.getElementById("red");

if (button1.checked){
    alert("radio1 selected");
}else if (button2.checked) {
    alert("radio2 selected");
}





</script>

@endsection
