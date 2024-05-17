$(document).ready(function(){
    $( '.form-select' ).select2({
        closeOnSelect: false,
        placeholder: 'Please select',
        width: '100%',
    });

    $("#submit").click((stay)=>{
        var form = $('#myForm');
        var redirecturl=form.attr('data-redirect');
        var formdata = form.serialize(); // here $(this) refere to the form its submitting
        $('#error_msg').html('');
        $('#successMessage').hide();
        
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: formdata, // here $(this) refers to the ajax object not form
            success: function (data) {
                if(typeof data!='undefined' && data!='' && data!=null){
                    if(typeof data.error!='undefined' && data.error!='' && data.error!=null){
                        jQuery.each(data.error,function(k,v){
                            $('#error_msg').append('<div class="alert alert-danger alert-dismissible fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>'+v+'</div>');
                        })
                    }else{
                        $('#successMessage').show();
                        setTimeout(() => {
                            window.location.href = (typeof redirecturl!='undefined' && redirecturl!='' && redirecturl!=null)?redirecturl:location.reload();
                        }, 2000);
                    }
                }
            },
        });
        stay.preventDefault(); 
    });
});
