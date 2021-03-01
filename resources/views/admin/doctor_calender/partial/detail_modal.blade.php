<div class="modal   detail_{!!$item->id!!}" tabindex="-1" role="dialog" aria-hidden="false" data-backdrop="false">
    <div class="modal-dialog modal-mg bbwith">
        <div class="modal-content" id="confirm">
            <div class="modal-header bblue">
                <h4 class="modal-title">Details</h4>
            </div>
            <?php
            $consecutive_doctor = $rota_detail[$date_index]->consecutive_doctor;
            $annual_doctor = $rota_detail[$date_index]->anual_leave_doctor;
            $special_rota_off= $rota_detail[$date_index]->special_rota_off;
            $conditions= $rota_detail[$date_index]->conditions;
        //    return $newconditions = jsondecode($conditions);
            $total_annual_doctor= 0 ;
            $total_consecutive_doctor = 0 ;
            $total_special_doctor = 0 ;
            $total_conditions = 0 ;


            if($annual_doctor){
                $total_annual_doctor = substr_count($annual_doctor , ",") +1;

            }

            if($consecutive_doctor){
                $total_consecutive_doctor = substr_count($consecutive_doctor, ",") +1;

             }

             if($special_rota_off){
                $total_special_doctor = substr_count($special_rota_off, ",") +1;

             }

             if($conditions){
                $total_conditions = substr_count($conditions, ",") +1;

             }


             $condition_ = json_decode(html_entity_decode( $rota_detail[$date_index]->conditions ) , TRUE);

             $conditon_s = "";
             $total_true = 0;
             $total_false = 0;

             foreach ($condition_ as $condition => $val) {
                if(!isset($conditions_key_values[$condition])){
                    continue;
                }
                if($val == 1){
                     $val = "TRUE";
                     $total_true = $total_true +1 ;
                    } else{
                    $val = "FALSE";
                    $total_false = $total_false +1 ;
                }
                $conditon_s .= "<tr><td>".$conditions_key_values[$condition] ." </td><td> ". $val ."</td></tr>";

                $correct_rota_percent = ($total_true/$total_conditions)* 100 ;
             }

            ?>
            <div class="modal-body bgdata">
                <div class="row">
                    <div id="" class="col-xs-12">
                        <div>
                            <ul class="point">
                                <li>
                                    <h3 class="Annuaal">Annual leave <span class="total">Total :
                                            {{$total_annual_doctor}} </span> </h3>
                                </li>
                            </ul>
                        </div>
                        <h4 class="myAnnuaal">{{$rota_detail[$date_index]->anual_leave_doctor}}</h4>
                    </div>
                    <div id="" class="col-xs-12">
                        <div>
                            <ul class="point">
                                <li>
                                    <h3 class="Consecutive">Consecutive doctors <span class="total">Total :
                                            {{$total_consecutive_doctor}} </span></h3>
                                </li>
                            </ul>
                        </div>
                        <h4 class="myConsecutive">{{$rota_detail[$date_index]->consecutive_doctor}}</h4>
                    </div>
                    <div id="" class="col-xs-12">
                        <div>
                            <ul class="point">
                                <li>
                                    <h3 class="Consecutive">Special Rota off doctors <span class="total">Total :
                                            {{$total_special_doctor}} </span></h3>
                                </li>
                            </ul>
                        </div>
                        <h4 class="myConsecutive">{{$rota_detail[$date_index]->special_rota_off}}</h4>
                    </div>

                    <div id="" class="col-xs-12">
                        <div>
                            <ul class="point">
                                <li>
                                    <h3 class="Consecutive"> Conditions <span class="total">
                                        {{-- Total :{{$total_conditions}} </span> --}}
                                        </h3>
                                </li>
                            </ul>
                        </div>
                        <div class="myConsecutive">
                            <table class="table-responsive table ">
                            {!!$conditon_s!!}
                            </table>
                        </div>

                        {{-- <h3 class="Consecutive"> Correct Rota Percentage</h3> --}}
                        {{-- <h4 class="myConsecutive"> {!!round($correct_rota_percent,2)!!} % </h4> --}}
                        {{-- {!!$correct_rota_percent!!}  --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>
