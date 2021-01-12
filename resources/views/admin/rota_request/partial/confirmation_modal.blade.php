<div id="msgmodal" class="modal {!! $req_status!!} " role="dialog">
    <div class="modal-dialog">
{{-- {!!$status!!} --}}
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <div id="my_msg_div">
                    Are you sure you want to {!!$status!!}
                    <button  data-dismiss="modal" onclick="update_status('{!!$leave->id!!}','{!!$status!!}')">Yes</button>
                    <button  data-dismiss="modal">Close</button>

                </div>


            </div>
        </div>

    </div>
</div>
