<script>
    $(function(){
console.log('myapp.blade.js');
@yield('document_ready_jq');

$('#open_time').timeEntry();
$('#close_time').timeEntry();
$("#defaultOpen").click();
$('#toggle-language').bootstrapToggle();
$('#toggle-access-in').bootstrapToggle();

$('a[name="activate_delete"]').on('click', function(e){
    var $form=$(this).closest('form');
    var current =this;
    e.preventDefault();
    var modal_heading = $(this).attr('modal_heading');
    var modal_msg = $(this).attr('modal_msg');
        console.log(modal_heading,modal_msg);
        if(modal_heading != '' || modal_heading!= undefined){
            $('#modal-heading').html(modal_heading);
        }
        if(modal_msg != '' || modal_msg!= undefined){
            $('#modal_msg').html(modal_msg);
        }
    $('#confirm').modal({ backdrop: 'static', keyboard: false })
        .one('click', '#delete', function() {
        console.log('asd');

        var my_url = $form.attr( 'action' );
        var my_method = $form.attr( 'method' );



        $.ajax({
            url: my_url,
            method: my_method,
            dataType: 'json',
            data: {'_token' :'{!! csrf_token() !!}'},
            success: function(data){
                if(data.action == 'update'){
                    $(current).find('span').html(data.new_value);
                }
                else if(data.action == 'delete'){
                    $(current).parent().parent().parent().remove();
                }

            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
        //	$form.trigger('submit');
    });
});

$('a[name="activate_delete_link"]').on('click', function(e){
    var current =this;
    e.preventDefault();
    var modal_heading = $(this).attr('modal_heading');
    var modal_msg = $(this).attr('modal_msg');
    var hit_url = $(this).attr('hit_url');
    var hit_method = $(this).attr('hit_method');
        console.log(modal_heading,modal_msg);
        if(modal_heading != '' || modal_heading!= undefined){
            $('#modal-heading').html(modal_heading);
        }
        if(modal_msg != '' || modal_msg!= undefined){
            $('#modal_msg').html(modal_msg);
        }
    $('#confirm').modal({ backdrop: 'static', keyboard: false })
        .one('click', '#delete', function() {

        var my_url = hit_url;
        var my_method = hit_method;

        $.ajax({
            url: my_url,
            method: my_method,
            dataType: 'json',
            data: {'_token' :'{!! csrf_token() !!}'},
            success: function(data){
                if(data.action == 'update'){
                    $(current).find('span').html(data.new_value);
                }
                else if(data.action == 'delete'){
                    $(current).parent().parent().remove();
                }

            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    });
});

$('a[name="delete"]').on('click', function(e){

var $form=$(this).closest('form');
e.preventDefault();
$('#confirm').modal({ backdrop: 'static', keyboard: false })
.one('click', '#delete', function() {
$form.trigger('submit');
});
});

$('span[name="map"]').on('click', function(e){
    e.preventDefault();
});
});
// end document ready funtion

$('.imgshow').on('click', function(e){

var my_src = $(this).attr('src');
$(".generalimgmodalsrc").attr("src", my_src);
$('.generalimgmodal').modal('show');
});

$('#Upload').on('click', function(e){
$('#uploadmodal').modal('show');
});

$('a[name="delete"]').on('click', function(e){

var $form=$(this).closest('form');
e.preventDefault();
$('#confirm').modal({ backdrop: 'static', keyboard: false })
.one('click', '#delete', function() {
$form.trigger('submit');
});
});

$(document).on('keyup','.arabic',function (){

var isArabic = /^([\u0600-\u06ff]|[\u0750-\u077f]|[\ufb50-\ufbc1]|[\ufbd3-\ufd3f]|[\ufd50-\ufd8f]|[\ufd92-\ufdc7]|[\ufe70-\ufefc]|[\ufdf0-\ufdfd]|[\n])*$/g;

var v = (this.value.replace(/ /g,''));
if(!(isArabic.test(v)===true)){
this.value='';
this.placeholder = 'Only Arabic Allowed'
}
});

$(document).on('keyup','.only_english',function (){
console.log(this.value);
//var isArabic = /^([\u0600-\u06ff]|[\u0750-\u077f]|[\ufb50-\ufbc1]|[\ufbd3-\ufd3f]|[\ufd50-\ufd8f]|[\ufd92-\ufdc7]|[\ufe70-\ufefc]|[\ufdf0-\ufdfd]|[\n])*$/g;
var isArabic = /[\u0600-\u06FF]/;

var v = (this.value.replace(/ /g,''));

if((isArabic.test(v)===true)){
    this.value='';
    this.placeholder = 'Only English Allowed'
}

});

function imagelimit(img){
    var f=img.files[0];
    var sizeInMb = f.size/(1024*1024);
    if(sizeInMb > 2)
    {
    $(".help-block").css('color', 'red');
    $(img).val(null);
    }
}

$('#image').change(function(){
var f=this.files[0];
var sizeInMb = f.size/(1024*1024);
if(sizeInMb > 2){
$(".help-block").css('color', 'red');
$('#image').val(null);
}
})

$('#avatar_1').change(function(){
var f=this.files[0];
var sizeInMb = f.size/(1024*1024);
if(sizeInMb > 2){
$(".help-block-avatar-1").css('color', 'red');
$('#avatar_1').val(null);
}
})

$('#avatar_2').change(function(){
var f=this.files[0];
var sizeInMb = f.size/(1024*1024);
if(sizeInMb > 2){
$(".help-block-avatar-2").css('color', 'red');
$('#avatar_2').val(null);
}
})

$('#avatar_3').change(function(){
var f=this.files[0];
var sizeInMb = f.size/(1024*1024);
if(sizeInMb > 2){
$(".help-block-avatar-3").css('color', 'red');
$('#avatar_3').val(null);
}
})

function excel(){
    var $form=$(this).closest('form');

var rdate = $('#reservationtime').val();
$('#hiddendate').val(rdate);
var id =$('#filter option:selected').val();
$('#hiddenfilter').val(id);

var id =$('#filter2 option:selected').val();
$('#hiddenfilter2').val(id);
id = $('#name').val();
$('#hiddden_name').val(id);

    e.preventDefault();
    var my_url = $form.attr( 'action' );
    var my_method = $form.attr( 'method' );

    $.ajax({
        url: my_url,
        method: my_method,
        dataType: 'json',
        data: {
            'hiddendate' :$('#hiddendate').val(),
            'hiddenfilter' :$('#hiddenfilter').val(),
            'hiddden_name' :$('#hiddden_name').val()
        },
        success: function(data){
            console.log('Sucess:', data);
        },
        error: function (data) {
            console.log('Error:', data);
        }
    });
}

function set_lat_long(lat , long , location){

            $('#lat').val('24.8607');
            $('#long').val('67.0011');
    // $('#lat').val(lat);
    // $('#long').val(long);
    $('#map-title').html('<b>Address: 	&nbsp;	&nbsp;</b>'+location);
    showPosition();return;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
        $('#map_error').html('');
    } else {
        $('#map_error').html('Geolocation is not supported by this browser.');
    }
}

function showPosition(position) {
    $('#direction_map').attr('href','https://www.google.com/maps/dir/Current+Location/'+$('#lat').val()+','+$('#long').val());
}

</script>
