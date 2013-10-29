$(document).ready(function(){
    //change cv sctive status
    $('.isActiveCv').click(function() {
        //cv id
        var cvId = $(this).attr('cvid');
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

    //change question show On CV status
    $('a.questionStatus').click(function(){
        var thisLink = $(this);
        var questionId = thisLink.attr('id') ;
        var linkStatus = thisLink.attr('status') ;
        var status = '';
        if(linkStatus == 'hide'){
            status = 0;
        }else{
            status = 1;
        }
        //hide the link
        thisLink.hide();
        //show loading div
        $('.loading').show();
        $.ajax({
            url: changeQuestionStatusUrl+'/'+questionId+'/'+status,
            success: function(msg) {
                if(msg == 'done'){
                    //change link status and text
                    if(status == 0){
                        thisLink.attr('status', 'show');
                        thisLink.text('show to companies');
                    }else{
                        thisLink.attr('status', 'hide');
                        thisLink.text('hide from companies');
                    }
                }
            },
            complete: function(msg) {
                //show the link
                thisLink.show();
                //hide loading div
                $('.loading').hide();
            }
        });
    });
    
    //show edit/add question answer form
    $('a.addEditAnswerLink').click(function(){
        var thisLink = $(this);
        var questionId = thisLink.attr('id');
        //show the input field
        $('#addEditQuestionAnswerDiv-'+questionId).toggle(500);
    });
    
    //add/edit form action
    $('button.addEditQuestionAnswerButton').click(function(){
        var thisInput = $(this);
        var questionId = thisInput.attr('id');
        //get the answer text
        var answerText = $.trim($('#addEditQuestionAnswer-'+questionId).val());
        if(answerText){
            //hide the button
            thisInput.hide();
            //show loading div
            $('.loading').show();
            $.ajax({
                url: addEditCompanyQuestionUrl+'/'+questionId+'/'+encodeURIComponent(answerText),
                success: function(msg) {
                    if(msg == 'done'){
                        //change the question answer
                        $('#questionAnswerSpan-'+questionId).text(answerText);
                    }
                },
                complete: function(msg) {
                    //show the button
                    thisInput.show();
                    //hide loading div
                    $('.loading').hide();
                }
            });
        
        }
    });
});