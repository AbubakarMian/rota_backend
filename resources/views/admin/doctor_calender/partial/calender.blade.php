<link href="{{ asset('css/doctor_calender.css') }}" rel="stylesheet">
<link href="{{ asset('css/calender.css') }}" rel="stylesheet">
<script src="{{ asset('theme/vendor/jquery/dist/jquery.js') }}"></script>
<script src="{{ asset('cssjs/jQuery-2.1.4.min.js')  }}"></script>
<script src="{{ asset('cssjs/jquery.slimscroll.min.js')  }}"></script>
<script src="{{ asset('cssjs/jquery.plugin.js')}}"></script>
<script src="{{ asset('theme/vendor/fastclick/lib/fastclick.js') }}"></script>
<script src="{{ asset('cssjs/jquery.timeentry.js')}}"></script>
<script src="{{ asset('theme/vendor/jquery.placeholder.js') }}"></script>
<style>

.doc{
    display: inline-block;
    /* margin-left: 3px; */
    padding: 0px 3px;
    font-size: 13px;
    font-weight: 500;
    color: #8b8b8b;
}
.doc:hover {
    transform: scale(1.12);
    cursor: pointer;
}

.rcorners2 {
  border-radius: 2px;
  /* border: 2px solid #73AD21; */
  padding:0px 4px;
  cursor: pointer;
  background: green;
    color: #fff !important;

}
.rcorners2:hover {

    transform: scale(1.12);
}

.higlightDutyDate{
    background:rgba(218, 221, 13, 0.205) !important;
}
.higlightDutyDate:hover {

}
.ucctext{
    color: #cd2ad8
}

</style>

{{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<div class="row bstmonthly" style="display: flex; justify-content: center;position: relative;width: 100%;">
    <div class="col-sm-12" style="margin-top: 20px;margin-left: 29px;">
        <a target="_blank" href="{{ asset('/admin/temp/rota/detail/'. $temp_rota->id ) }}"
            class="btn btn-info gren">Details</a>
            <a href="{{ url('admin/rota/save/temp/'.$temp_rota->id)  }}" class="btn btn-primary grexe" type="submit" >Save To Rota
            </a>
            {{-- <input type="submit" value="save"  class="btn btn-primary"> --}}
        &nbsp; <button class="btn btn-warning hucc" id="hide_ucc">Hide UCC</button>
        &nbsp; <button class="btn btn-primary hrducc" id="hide_regularduites">Hide Regular Duties</button>
        <a href="{{ url()->previous() }}" class="btn btn-primary bckk" type="button" >Back</a>
    </div>
    <div class="col-sm-8" style="float: left">
        <h2 class="">

            <div class="mydoctortable">" {!!sizeof($doctors)!!} DOCTOR's ROTA {!!date("F", mktime(0, 0, 0, $temp_rota->monthly_rota->month, 10)).' '.$temp_rota->monthly_rota->year!!} <span class="demospan">(Demo-{!!$temp_rota->demo_num!!})</span>"</div>

        </h2>


    </div>
</div>
<div class="table-responsive fullbox" id="mytableareaa" style="height: auto;display:none;">
    <table class="table table-striped table table-hover table table-bordered table table-condensed" id="customers">
        <thead class="monday">
            @foreach($weekdays as $weekday)
            <th>{!!$weekday!!}</th>
            @endforeach
        </thead>

        <tbody id="calenderdates">
            <tr class="myboxes">
                <?php $tds = 0;
                     $rota_generate_pattern = $temp_rota->rota_generate_pattern;
                ?>
                @foreach ($temp_rota->rota_generate_pattern as $date_index=>$item)

                <?php
                $morning_doctors = $temp_rota->doctors()
                ->where('shift','morning')->where('duty_date',$item->duty_date)->get(['doctor_id','is_ucc','doctor_type_id'])->toArray();

                $evening_doctors = $temp_rota->doctors()
                ->where('shift','evening')->where('duty_date',$item->duty_date)->get(['doctor_id','is_ucc','doctor_type_id'])->toArray();

                $night_doctors = $temp_rota->doctors()
                ->where('shift','night')->where('duty_date',$item->duty_date)->get(['doctor_id','is_ucc','doctor_type_id'])->toArray();
                list($ucc_morning_doctor_id,$ucc_morning_doctor_name,$all_morning_doctor) = sort_by_name_get_ucc($morning_doctors,$doctors_by_id);
                list($ucc_evening_doctor_id,$ucc_evening_doctor_name,$all_evening_doctor) = sort_by_name_get_ucc($evening_doctors,$doctors_by_id);
                list($ucc_night_doctor_id,$ucc_night_doctor_name,$all_night_doctor) = sort_by_name_get_ucc($night_doctors,$doctors_by_id);

                if($tds == 1){
                    echo '<tr class="myboxes">';
                }

                $rota_detail = $temp_rota->rota_Date_Detail->where('date',$item->duty_date);
            ?>
                @if($date_index === 0)
                <?php $tds = $start_weekday; ?>
                @for($i = $start_weekday ; $i>1; $i-- )
                <td></td>
                @endfor
                @endif
                <td>
                    <div class="mydatearrow">
                        <div class="mydate">{!!($date_index+1)!!}</div>
                        <span class="demodal ucc detail_{!!$item->id!!}" data-toggle="modal"
                            data-target=".detail_{!!$item->id!!}">Detail</span>
                        @include('admin.doctor_calender.partial.detail_modal')
                    </div>
                    <div class="mybigmorning">
                        <div class="morningdoctor">
                            <h5 class="mydoctor">Morning
                            </h5>
                            <div class="">
                                <div class="col-sm-12 textMorningList">
                                    <div class="multiple_line_text_morning_{!!$item->id!!}"> {!!$all_morning_doctor!!}</div>
                                    <div class="ucc_morning_{!!$item->id!!} ucctext">{!! $ucc_morning_doctor_name !!}</div>
                                </div>
                                <div class="row" style="margin: 2px">
                                    <div class="col-sm-6 regular_duties">

                                        <select id="dates-field2"
                                            onchange="show_doctors('multiple_line_text_morning_{!!$item->id!!}','{!!$temp_rota->id!!}','{!!$item->duty_date!!}','morning','0',this);"
                                            class="multiselect-ui form-control" multiple="multiple" cols="2" rows="2">
                                            @foreach ($doctors as $doctor)
                                            <?php
                                            $selected = '';

                                            if(in_array($doctor->id,array_column($morning_doctors,'doctor_id'))){
                                                $selected = 'selected';
                                            }
                                        ?>
                                            <option {!! $selected !!} value="{!!$doctor->id!!}">
                                                {!!ucwords($doctor->user->name)!!}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 ucc_class">
                                        <select id="myucc" class="form-control myucc"
                                        onchange="show_doctors('ucc_morning_{!!$item->id!!}','{!!$temp_rota->id!!}','{!!$item->duty_date!!}','morning','1',this);"
                                            class="multiple_line form-control">
                                            <option value="">Ucc</option>
                                            @foreach ($doctors as $doctor)
                                            <?php
                                                $selected = '';

                                                if($ucc_morning_doctor_id==$doctor->id){
                                                    $selected = 'selected';
                                                }
                                            ?>
                                            <option {!! $selected !!} value="{!!$doctor->id!!}">
                                                {!!ucwords($doctor->user->name)!!}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="eveningdoctor">
                            <h5 class="mydoctor">Evening
                            </h5>
                            <div class="">
                                <div class="col-sm-12 textEveningList">
                                    <div class="multiple_line_text_evening_{!!$item->id!!}"> {!!$all_evening_doctor!!}</div>
                                    <div class="ucc_evening_{!!$item->id!!} ucctext">{!! $ucc_evening_doctor_name !!}</div>
                                </div>
                                <div class="row" style="margin: 2px">
                                    <div class="col-sm-6 regular_duties">
                                        <select id="dates-field2"
                                            onchange="show_doctors('multiple_line_text_evening_{!!$item->id!!}','{!!$temp_rota->id!!}','{!!$item->duty_date!!}','evening','0',this);"
                                            class="multiselect-ui form-control" multiple="multiple" cols="2" rows="2">
                                            @foreach ($doctors as $doctor)
                                            <?php
                                            $selected = '';

                                            if(in_array($doctor->id,array_column($evening_doctors,'doctor_id'))){
                                                $selected = 'selected';
                                            }
                                        ?>
                                            <option {!! $selected !!} value="{!!$doctor->id!!}">
                                                {!!ucwords($doctor->user->name)!!}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6 ucc_class">
                                        <select id="myucc" class="myucc form-control"
                                            onchange="show_doctors('ucc_evening_{!!$item->id!!}','{!!$temp_rota->id!!}','{!!$item->duty_date!!}','evening','1',this);"
                                            class="multiple_line form-control">
                                            <option value="">Ucc</option>
                                            @foreach ($doctors as $doctor)
                                            <?php
                                            $selected = '';
                                            // if(in_array($ucc_evening_doctor,array_column($evening_doctors,'doctor_id'))){
                                            if($ucc_evening_doctor_id==$doctor->id){
                                                $selected = 'selected';
                                            }
                                        ?>
                                            <option {!! $selected !!} value="{!!$doctor->id!!}">
                                                {!!ucwords($doctor->user->name)!!}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nightdoctor">
                            <h5 class="mydoctor">Night </h5>
                            <div class="">
                                <div class="col-sm-12 textNightList">
                                    <div class="multiple_line_text_night_{!!$item->id!!}"> {!!$all_night_doctor!!}</div>
                                    <div class="ucc_night_{!!$item->id!!} ucctext">{!! $ucc_night_doctor_name !!}</div>
                                </div>
                                <div class="col-sm-6 regular_duties">

                                    <select id="dates-field2"
                                        onchange="show_doctors('multiple_line_text_night_{!!$item->id!!}','{!!$temp_rota->id!!}','{!!$item->duty_date!!}','night','0',this);"
                                        class="multiselect-ui form-control" multiple="multiple" cols="2" rows="2">
                                        @foreach ($doctors as $doctor)
                                        <?php
                                        $selected = '';

                                        if(in_array($doctor->id,array_column($night_doctors,'doctor_id'))){
                                            $selected = 'selected';
                                        }
                                    ?>
                                        <option {!! $selected !!} value="{!!$doctor->id!!}">
                                            {!!ucwords($doctor->user->name)!!}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6 ucc_class">
                                    <select id="myucc" class="myucc form-control"
                                        onchange="show_doctors('ucc_night_{!!$item->id!!}','{!!$temp_rota->id!!}','{!!$item->duty_date!!}','night','1',this);"
                                        class="multiple_line form-control">
                                        <option value="">Ucc</option>
                                        @foreach ($doctors as $doctor)
                                        <?php
                                            $selected = '';
                                            if($ucc_night_doctor_id==$doctor->id){
                                                $selected = 'selected';
                                            }
                                        ?>
                                        <option {!! $selected !!} value="{!!$doctor->id!!}">
                                            {!!ucwords($doctor->user->name)!!}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <?php
                    if($tds == 7){
                        echo '</tr>';
                        $tds = 1;
                    }else{
                        $tds = $tds + 1;
                    }

                ?>
                @endforeach
                @for(;$tds<8;$tds++) <td>
                    </td>
                    @endfor

        </tbody>
    </table>
</div>
<div id="loader">
    <center><h1 style="color: red">Generating Rota Please Wait ...</h1></center>
</div>

@section('app_jquery')
<script>
    $(function(){
        setTimeout(function(){
            hide_information();
            $('#mytableareaa').toggle();
            $('#loader').toggle();
    },2000)
    })
var selected_doctors = '';

function show_doctors(show_list,temp_rota_id,duty_date,shift,is_ucc,sel){ //,'{!!$temp_rota->id!!}','{!!$item->duty_date!!}','morning','0',this
var len = sel.options.length;
var opts = '',opt,text_name='',glue= '';

  for (var i = 0; i < len; i++) {
    opt = sel.options[i];

    if (opt.selected) {
      opts = opts+glue+opt.value;
      console.log('sel are ',opt.value);
      console.log('text are ',opt.text);
      text_name = text_name+glue+opt.text;
      glue = ',';
    }
  }

  $.ajax({
        url:'{!!asset("admin/temp_rota/calender/update")!!}',
        method: 'POST',
        dataType: 'json',
        data: {
            '_token' :'{!! csrf_token() !!}',
            'doctors':opts,
            temp_rota_id:temp_rota_id,
            duty_date:duty_date,
            shift:shift,
            is_ucc:is_ucc,
        },
        success: function(data){
            $('.'+show_list).html(data.response);
        },
        error: function (data) {
            console.log('Error:', data);
        }
    });

        // $('.'+show_list).html(text_name);
}

function hide_information(){
    $('#hide_ucc').click();
    $('#hide_regularduites').click();
}

</script>

<script>

var prev_id = 0 ;

    $('#hide_ucc').on('click' , function(){
    $('.myucc').toggle();
    $('.ucc_class').toggle();
    $("#hide_ucc").toggleText('View UCC', 'Update UCC');
    $('.regular_duties').toggleClass('col-sm-6').toggleClass('col-sm-12');
});


$('#hide_regularduites').on('click' , function(){
    $("#hide_regularduites").toggleText('View Regular Duties', 'Update Regular Duties');
    $('.ucc_class').toggleClass('col-sm-6').toggleClass('col-sm-12');
    $('.multiselect').toggle();
});
$(".doc").on('click' , function(){
    var id = $(this).data('id');

    //  $('[data-id="'+prev_id+'"]').css('color' , '');
     $('.higlightDutyDate').removeClass('higlightDutyDate');
     $('[data-id="'+prev_id+'"]').removeClass('rcorners2');

    //  $('[data-id="'+id+'"]').css('color' , 'red');
     $('[data-id="'+id+'"]').addClass('rcorners2');


     var td = $( '[data-id="'+id+'"]' ).parent().parent();//.parent().parent().parent().parent()

     td.addClass('higlightDutyDate');

        if( prev_id == id){
            $('.higlightDutyDate').removeClass('higlightDutyDate');
            $('[data-id="'+prev_id+'"]').removeClass('rcorners2');
            prev_id = 0;
        }
        else{
            prev_id = id;
        }
});

function closeModal(){
    $('.modal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
}

$.fn.extend({
    toggleText: function(a, b){
        return this.text(this.text() == b ? a : b);
    }
});

$.fn.extend({
    toggleColor: function(a, b){
        return this.css( 'color', this.text() == b ? a : b);
    }
});

</script>

<?php

function sort_by_name_get_ucc($doctors,$doctors_by_id){
    $m_doctors = [];
    $ucc_doctor_id = 0;
    $ucc_doctor_name = '';
    foreach ($doctors as $key => $doctor) {
        if($doctor['is_ucc']){
                $ucc_doctor_id = $doctor['doctor_id'];
                $ucc_doctor_name = ucwords($doctors_by_id[$ucc_doctor_id]);
        }
        else{
            $m_doctors[$doctors_by_id[$doctor['doctor_id']]] =  [
            'name'=>ucwords($doctors_by_id[$doctor['doctor_id']]),
            'doctor_type_id'=>$doctor['doctor_type_id'],
            'id'=>$doctor['doctor_id'],
            ];
        }
    }
    asort($m_doctors);
    $all_doctors = '';
    foreach($m_doctors as $doctor_id=>$md){
        $style='';
        if($md['doctor_type_id'] == 2){
            $style = 'color:#8b8b8b';
        }
        $all_doctors .= ',<div style="'.$style.'" data-id="'.$md['id'].'" class=" doc did_'.$md['id'].'">'.$md['name'].'</div>';
    }
    $all_doctors = ltrim($all_doctors,',');
    return [$ucc_doctor_id,$ucc_doctor_name,$all_doctors];
}

?>

@include('admin.doctor_calender.partial.calenderjs')
@endsection
