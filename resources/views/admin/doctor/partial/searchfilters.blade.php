 <div class="col-xs-4">
     <div class="form-group">
         <label>Search Name</label>
         <div class="mb15">
             <fieldset>
                 <div class="control-group">
                     <div class="controls">
                         <div class="input-prepend input-group">
                             <span class="add-on input-group-addon"></span>
                             {!! Form::text('name',$name ??null, ['class' => 'form-control',
                             'data-parsley-required'=>'true',
                             'data-parsley-trigger'=>'change',
                             'placeholder'=>'Enter Name','id'=>'name']) !!}
                         </div>
                     </div>
                 </div>
             </fieldset>
         </div>
     </div>
 </div>

 <div class="col-md-2 pull-left">
     <div class="form-group text-center">
         <label></label>
         <div>
             <input type="submit" class="btn btn-info pull-left date-range-review-btn"
                    value="Search"
                    {{--onclick ="return search();" --}}
             >
         </div>
     </div>
 </div>
