<link href="{{ asset('css/calender.css') }}" rel="stylesheet">
<h1 class="monthly">

    <center>
        <div class="sticky"> " DOCTOR's MONTHLY TABLE "</div>
    </center>


</h1>
<h3 class="navigatorCalendar">
    <a href="" class="btn btn-promary btn-calender"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
    <a href="" class="btn btn-promary btn-calender"><i class="fa fa-arrow-right" aria-hidden="true"></i></a>
</h3>

<div class="table-responsive" id="mytable">
    <table class="table table-striped table table-hover table table-bordered table table-condensed" id="customers">
        <thead class="monday">
            @foreach($weekdays as $weekday)
            <th>{!!$weekday!!}</th>
            @endforeach

        </thead>
        <tbody id="calenderdates">
            <tr class="myboxes">
                <?php $tds = 0; ?>
                @foreach ($list as $key=>$item)

                <?php
                if($tds == 1){
                    echo '<tr class="myboxes">';
                }

            ?>

                @if($key === 0)
                <?php $tds = $start_weekday; ?>
                @for($i = $start_weekday ; $i>1; $i-- )
                <td></td>
                @endfor
                @endif
                <td>
                    <div class="mydateArea">
                        <div class="mydate">{!!($key+1)!!}</div>
                        <div class="ucc">UCC</div>
                    </div>

                    <div class="bigmorning">

                        <div class="morning">
                            <input type="number" placeholder="Morning " class="mymorning morningvalue_{!!$item->id!!}"
                                name="total_morning_doctors" value="{!!$item->total_morning_doctors!!}"
                                onchange="update_ajax('{!!$item->id!!}');">


                            <div class="switch">
                                <input id="morning_ucc-{{$item->id}}" type="checkbox"
                                    class="switch-input morning_ucc_value_{!!$item->id!!}"
                                    onchange="update_ajax('{!!$item->id!!}');" {!!$item->has_morning_ucc ?
                                'checked':''!!}
                                name="morning_ucc" value="{!!$item->has_ucc!!}">
                                <label for="morning_ucc-{{$item->id}}" class="switch-label"
                                    value="{!!$item->has_morning_ucc!!}"></label>
                            </div>

                        </div>

                        <div class="morning">
                            <input type="number" placeholder="Evening" class="mymorning eveningvalue_{!!$item->id!!}"
                                name="total_evening_doctors" value="{!!$item->total_evening_doctors!!}"
                                onchange="update_ajax('{!!$item->id!!}');">


                            <div class="switch">
                                <input id="evening_ucc-{{$item->id}}" type="checkbox"
                                    class="switch-input evening_ucc_value_{!!$item->id!!}"
                                    onchange="update_ajax('{!!$item->id!!}');" {!!$item->has_evening_ucc ?
                                'checked':''!!}
                                name="evening_ucc" value="{!!$item->has_ucc!!}">
                                <label for="evening_ucc-{{$item->id}}" class="switch-label"
                                    value="{!!$item->has_ucc!!}"></label>
                            </div>
                        </div>

                        <div class="morning">
                            <input type="number" placeholder="Night" class="mymorning nightvalue_{!!$item->id!!}"
                                name="total_night_doctors" value="{!!$item->total_night_doctors!!}"
                                onchange="update_ajax('{!!$item->id!!}');">


                            <div class="switch">
                                <input id="night_ucc-{{$item->id}}" type="checkbox"
                                    class="switch-input night_ucc_value_{!!$item->id!!} "
                                    onchange="update_ajax('{!!$item->id!!}');" {!!$item->has_night_ucc ? 'checked':''!!}
                                name="night_ucc" value="{!!$item->has_ucc!!}">
                                <label for="night_ucc-{{$item->id}}" class="switch-label"
                                    value="{!!$item->has_ucc!!}"></label>
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
        </tbody>
    </table>
</div>

@section('app_jquery')
<script>
    function update_ajax(id){
        console.log('asd',$('.morning_ucc_value_'+id).is(':checked'));
        // alert($('.morningvalue_'+id).val());
        // alert($('.eveningvalue_'+id).val());
        // alert($('.nightvalue_'+id).val());
        var my_url = "{!!asset('admin/generatepattern/postajax')!!}/"+id;
        // console.log('gdfsg',my_url);
        $.ajax({
        url: my_url,
        method: 'POST',
        dataType: 'json',
        data: {
            'total_morning_doctors' :$('.morningvalue_'+id).val(),
            'total_evening_doctors' :$('.eveningvalue_'+id).val(),
            'total_night_doctors' :$('.nightvalue_'+id).val(),
            'has_morning_ucc' : $('.morning_ucc_value_'+id).is(':checked')?1:0,
            'has_evening_ucc' : $('.evening_ucc_value_'+id).is(':checked')?1:0,
            'has_night_ucc' : $('.night_ucc_value_'+id).is(':checked')?1:0,

            '_token':'{!!csrf_token()!!}'
        },
        success: function(data){
            console.log('Sucess:', data);
        },
        error: function (data) {
            console.log('Error:', data);
        }
        });

    }

</script>
@endsection
