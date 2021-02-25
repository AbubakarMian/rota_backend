<div class="modal   detail_{!!$item->id!!}"  tabindex="-1" role="dialog" aria-hidden="false" data-backdrop="false">
    <div class="modal-dialog modal-mg ">
        <div class="modal-content" id="confirm">
            <div class="modal-header">
                <h4 class="modal-title">Details detail_{!!$item->id!!}</h4>
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
            <div class="modal-body">
                <div class="row">
                    <div id="" class="col-xs-12">
                    <div> 
                        <h3>Annual leave doctors </h3> 
                        <span>Total doctors : {{$total_annual_doctor}} </span> 
                    </div>
                        <h4>{{$rota_detail[$date_index]->anual_leave_doctor}}</h4>
                    </div>
                    <div id="" class="col-xs-12">
                        <div>
                            <h3>Consecutive doctors</h3>
                            <span>Total doctors : {{$total_consecutive_doctor}} </span>
                        </div>  
                        <h4>{{$rota_detail[$date_index]->consecutive_doctor}}</h4>
                        
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>

