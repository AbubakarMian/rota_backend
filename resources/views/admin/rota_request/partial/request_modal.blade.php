
<div id="msgmodal" class="modal fade" role="dialog" aria-hidden="false">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Request Detail</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped mg-t editable-datatable">

                    <thead>
                    <tr>
                        <th>Duty date</th>
                        <th>Weekday</th>

                    </tr>
                    </thead>
                    <tbody id="my-modal-table">

                        <tr>
                            <td>{!! $q->duty_date !!}</td>
                            <td>{!! $q->week_day_id!!}</td>

                        </tr>

                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>

