$(document).ready(function(){

   //get progress bar value
   var v=$(".current-rating").attr("value");
   //call progress bar script and show it
  // $("#progressbar").progressbar({ value: parseInt(v) });

   //change cv sctive status
    $('.isActiveCv').click(function() {
        //cv id
        var cvId = $(this).attr('id');
        var status = '';
        var thisCheckbox = $(this);
        $(this).attr('disabled','disabled')
        if($(this).is(':checked')) {
            status = 1;
        } else {
            status = 0;
        }
        //show loading div
        $('.loading').show();
        $.ajax({
            url: changeCvStatusUrl+'/'+cvId+'/'+status,
            success: function(msg) {
            },
            complete: function(msg) {
                thisCheckbox.removeAttr('disabled')
                //hide loading div
                $('.loading').hide();
            }
        });
    });


});