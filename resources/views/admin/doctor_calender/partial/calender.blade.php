<link href="{{ asset('css/doctor_calender.css') }}" rel="stylesheet">
<link href="{{ asset('css/calender.css') }}" rel="stylesheet">
<script src="{{ asset('theme/vendor/jquery/dist/jquery.js') }}"></script>
<script src="{{ asset('cssjs/jQuery-2.1.4.min.js')  }}"></script>
<script src="{{ asset('cssjs/jquery.slimscroll.min.js')  }}"></script>
<script src="{{ asset('cssjs/jquery.plugin.js')}}"></script>
<script src="{{ asset('theme/vendor/fastclick/lib/fastclick.js') }}"></script>
<script src="{{ asset('cssjs/jquery.timeentry.js')}}"></script>
<script src="{{ asset('theme/vendor/jquery.placeholder.js') }}"></script>

{{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<div class="row bstmonthly" style="display: flex; justify-content: center;position: relative;width: 100%;">
    <div class="col-sm-4" style="margin-top: 20px;margin-left: 29px;">
        <a target="_blank" href="{{ asset('/admin/temp/rota/detail/'. $temp_rota->id ) }}"
            class="btn btn-info">Details</a>
    </div>
    <div class="col-sm-8" style="float: left">
        <h2 class="">

            <div class="mydoctortable"> " DOCTOR's MONTHLY TABLE " Demo-{!!$temp_rota->demo_num!!}</div>

        </h2>
    </div>
</div>

{{-- <div class="row" style="margin-bottom: 30px">
    <div style="margin-left: 10px">
        <a href="{{ asset('/admin/temp/rota/detail/'. $temp_rota->monthly_rota_id ) }}" class="btn
btn-info">Details</a>
</div>
<div>
    <center>
        <h2 class="bstmonthly">

            <div class="mydoctortable"> " DOCTOR's MONTHLY TABLE "</div>

        </h2>
    </center>
</div>
</div> --}}


<div class="table-responsive " id="mytableareaa" style="height: auto">
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
                ->where('shift','morning')->where('duty_date',$item->duty_date)->get(['doctor_id','is_ucc'])->toArray();

                $evening_doctors = $temp_rota->doctors()
                ->where('shift','evening')->where('duty_date',$item->duty_date)->get(['doctor_id','is_ucc'])->toArray();

                $night_doctors = $temp_rota->doctors()
                ->where('shift','night')->where('duty_date',$item->duty_date)->get(['doctor_id','is_ucc'])->toArray();

                $ucc_morning_doctor = '';
                $all_morning_doctor = '';
                foreach ($morning_doctors as $key => $doctor) {
                                                if($doctor['is_ucc']){
                                                    $ucc_morning_doctor = $doctor['doctor_id'];
                                                }

                                                $all_morning_doctor = $all_morning_doctor.' , '.$doctors_by_id[$doctor['doctor_id']];
                                            }
                                            $all_morning_doctor = preg_replace('/ , /', '', $all_morning_doctor, 1);
                                            $ucc_evening_doctor = '';
                $all_evening_doctor = '';
                foreach ($evening_doctors as $key => $doctor) {
                                                if($doctor['is_ucc']){
                                                    $ucc_evening_doctor = $doctor['doctor_id'];
                                                }
                                                $all_evening_doctor = $all_evening_doctor.' , '.$doctors_by_id[$doctor['doctor_id']];

                                            }
                                            $all_evening_doctor = preg_replace('/ , /', '', $all_evening_doctor, 1);

                $ucc_night_doctor = '';
                $all_night_doctor = '';
                foreach ($night_doctors as $key => $doctor) {
                                                if($doctor['is_ucc']){
                                                    $ucc_night_doctor = $doctor['doctor_id'];
                                                }
                                                 $all_night_doctor = $all_night_doctor.' , '.$doctors_by_id[$doctor['doctor_id']];
                                            }
                                            $all_night_doctor = preg_replace('/ , /', '', $all_night_doctor, 1);

                if($tds == 1){
                    echo '<tr class="myboxes">';
                }
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
                        <span class="ucc detail_{!!$item->id!!}" data-toggle="modal" data-target=".detail_{!!$item->id!!}">Detail</span>
                        @include('admin.doctor_calender.partial.detail_modal',['item'])
                    </div>
                    <div class="mybigmorning">
                        <div class="morningdoctor">
                            <h5 class="mydoctor">Morning
                            </h5>
                            <div class="">
                                <div class="col-sm-12 textMorningList">
                                    <div class="multiple_line_text_morning_{!!$item->id!!}"> {!!$all_morning_doctor!!}
                                    </div>
                                </div>
                                <div class="row" style="margin: 2px">
                                    <div class="col-sm-6">

                                        <select id="dates-field2"
                                            onchange="show_doctors('multiple_line_text_night_{!!$item->id!!}');"
                                            class="multiselect-ui form-control" multiple="multiple" cols="2" rows="2"
                                            >
                                            @foreach ($doctors as $doctor)
                                            <?php
                                            $selected = '';

                                            if(in_array($doctor->id,array_column($night_doctors,'doctor_id'))){
                                                $selected = 'selected';
                                            }
                                        ?>
                                            <option {!! $selected !!} value="{!!$doctor->id!!}">{!!$doctor->user->name!!}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <select id="myucc" class="multiple_line form-control">
                                            <option value="">Ucc</option>
                                            @foreach ($doctors as $doctor)
                                            <?php
                                                $selected = '';

                                                if($ucc_morning_doctor==$doctor->id){

                                                    $selected = 'selected';
                                                }
                                            ?>
                                            <option {!! $selected !!} value="{!!$doctor->id!!}">{!!$doctor->user->name!!}
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
                                    <div class="multiple_line_text_evening_{!!$item->id!!}"> {!!$all_evening_doctor!!}
                                    </div>
                                </div>
                               <div class="row" style="margin: 2px">
                                <div class="col-sm-6">
                                    <select id="dates-field2"
                                        onchange="show_doctors('multiple_line_text_evening_{!!$item->id!!}');"
                                        class="multiselect-ui form-control" multiple="multiple" cols="2" rows="2">
                                        @foreach ($doctors as $doctor)
                                        <?php
                                            $selected = '';

                                            if(in_array($doctor->id,array_column($evening_doctors,'doctor_id'))){
                                                $selected = 'selected';
                                            }
                                        ?>
                                        <option {!! $selected !!} value="{!!$doctor->id!!}">{!!$doctor->user->name!!}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <select id="myucc" class="multiple_line form-control">
                                        <option value="">Ucc</option>
                                        @foreach ($doctors as $doctor)
                                        <?php
                                            $selected = '';
                                            // if(in_array($ucc_evening_doctor,array_column($evening_doctors,'doctor_id'))){
                                            if($ucc_evening_doctor==$doctor->id){
                                                $selected = 'selected';
                                            }
                                        ?>
                                        <option {!! $selected !!} value="{!!$doctor->id!!}">{!!$doctor->user->name!!}
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
                                </div>
                                <div class="col-sm-6">

                                    <select id="dates-field2"
                                        onchange="show_doctors('multiple_line_text_night_{!!$item->id!!}');"
                                        class="multiselect-ui form-control" multiple="multiple" cols="2" rows="2"
                                        >
                                        @foreach ($doctors as $doctor)
                                        <?php
                                        $selected = '';

                                        if(in_array($doctor->id,array_column($night_doctors,'doctor_id'))){
                                            $selected = 'selected';
                                        }
                                    ?>
                                        <option {!! $selected !!} value="{!!$doctor->id!!}">{!!$doctor->user->name!!}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <select id="myucc" class="multiple_line form-control">
                                        @foreach ($doctors as $doctor)
                                        <option value="">Ucc</option>
                                        <?php
                                            $selected = '';
                                            if($ucc_night_doctor==$doctor->id){
                                                $selected = 'selected';
                                            }
                                        ?>
                                        <option {!! $selected !!} value="{!!$doctor->id!!}">{!!$doctor->user->name!!}
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

@section('app_jquery')
<script>
    var selected_doctors = '';

function show_doctors(show_list){
    setTimeout(function(){
        $('.'+show_list).html(selected_doctors);
        console.log('e asdsa',selected_doctors);
    },2000)
}
</script>
@include('admin.doctor_calender.partial.calenderjs')
@endsection
