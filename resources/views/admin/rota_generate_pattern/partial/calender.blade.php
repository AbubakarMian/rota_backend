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
                        <input type="number" placeholder="Morning" class="mymorning" name="total_morning_doctors"
                            value="{!!$item->total_morning_doctors!!}">
                        <div class="switch">
                            <input id="switch-1" type="checkbox" class="switch-input">
                            <label for="switch-1" class="switch-label"></label>
                        </div>
                    </div>

                    <div class="morning">
                        <input type="number" placeholder="Evening" class="mymorning" name="total_evening_doctors"
                            value="{!!$item->total_evening_doctors!!}">
                        <div class="switch">
                            <input id="switch-2" type="checkbox" class="switch-input">
                            <label for="switch-2" class="switch-label"></label>
                        </div>
                    </div>

                    <div class="morning">
                        <input type="number" placeholder="Night" class="mymorning" name="total_night_doctors"
                            value="{!!$item->total_night_doctors!!}">
                        <div class="switch">
                            <input id="switch-3" type="checkbox" class="switch-input">
                            <label for="switch-3" class="switch-label"></label>
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
    populate_calender();

    function populate_calender(){
        $('#calenderdates').append(append_tr());
    }

    function append_tr(){
        return (
        ``
        );
    }


</script>
@endsection
