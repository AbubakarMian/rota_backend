<div class="modal   detail_{!!$item->id!!}"  tabindex="-1" role="dialog" aria-hidden="false" data-backdrop="false">
    <div class="modal-dialog modal-mg bbwith">
        <div class="modal-content" id="confirm">
            <div class="modal-header bblue">
                <h4 class="modal-title">Details</h4>
            </div>
            <?php
            $consecutive_doctor = $rota_detail[$date_index]->consecutive_doctor;
            $annual_doctor = $rota_detail[$date_index]->anual_leave_doctor;
            $total_annual_doctor= 0 ;
            $total_consecutive_doctor = 0 ;

            if($annual_doctor){
                $total_annual_doctor = substr_count($annual_doctor , ",") +1;

            }

            if($consecutive_doctor){
                $total_consecutive_doctor = substr_count($consecutive_doctor, ",") +1;

             }
            ?>
            <div class="modal-body bgdata">
                <div class="row">
                    <div id="" class="col-xs-12">
                    <div>
                        <ul class="point">
                            <li>
                                <h3 class="Annuaal">Annual leave <span class="total">Total : {{$total_annual_doctor}} </span> </h3>
                            </li>
                        </ul>
                    </div>
                        <h4 class="myAnnuaal">{{$rota_detail[$date_index]->anual_leave_doctor}}</h4>
                    </div>
                    <div id="" class="col-xs-12">
                        <div>
                            <ul class="point">
                                <li>
                            <h3 class="Consecutive">Consecutive doctors <span class="total">Total : {{$total_consecutive_doctor}} </span></h3>
                                </li>
                            </ul>
                        </div>
                        <h4 class="myConsecutive">{{$rota_detail[$date_index]->consecutive_doctor}}</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>

