$(document).ready(function(){

    $('.taskNote').hide();
    $('.edittaskNote').hide();
    $(".status").val(taskStatus);

   //get progress bar value
   var v=$(".current-rating").attr("value");
   if(v)
   //call progress bar script and show it
   $("#progressbar").progressbar({ value: parseInt(v) });

    //ajax action that adds note
    $('.addNoteAjax').click(function(){
        $('.taskNote').toggle(500);
    });
    $('.addNoteButton').live("click", function(){
        if($('.noteText1').val()){
            $(".loading").show();
        $.ajax({
            url: addNoteUrl+$('.noteText1').val()+"/"+taskId,
            success: function(msg) {
                $('.noteDiv').append(msg);
            },
            complete: function(msg) {
                 $('.taskNote').hide();
                 $('.edittaskNote').hide();
                 $('.noteText1').val("");
                 $(".loading").hide();
            }
        });
        //alert($('.noteText').val());
        }
        else{

        }

    });


   //ajax action that edits note
    $('.editNoteAjax').live("click",function(){
        $(this).next().toggle(500);
    });

    $('.editNoteButton').live("click",function(){

        if($(this).prev().val()){
                $(".loading").show();
                myclass='.'+$(this).prev().attr("noteId")+'note';
                text= $(this).prev().val();
        $.ajax({
            url: editNoteUrl+"/"+$(this).prev().val()+"/"+taskId+"/"+$(this).prev().attr("noteId"),
            success: function(msg) {

            },
            complete: function(msg) {
                 //alert(myclass);
                 $(myclass).text("");
                 $(myclass).text(text);
                 //$('.noteDiv').append('note <b> -- </b> <a href=" javascript:void(0)" noteid="1" noteclass="editNoteAjax">Edit Note</a>');
                 $(this).parent().hide();
                 $(".loading").hide();

            }
        });

        //alert($('.noteText').val());
        }
        else{

        }

    });


    //ChangeTask Status
    $('.status').change(function(){
        
        status= $(".status option:selected").val();
        alert(status);
        var lastStatus=$('#taskstatus').attr("class");
        //alert(status);
            $.ajax({
            url: changeStatusUrl+"/"+taskId+"/"+status,
            success: function(msg) {

            },
            complete: function(msg) {
                if(status == 'done'){
                    $('#taskstatus').removeClass(lastStatus).addClass(status);
                }
                if(status == 'new'){
                    $('#taskstatus').removeClass(lastStatus).addClass(status);
                }
                if(status == 'inprogress'){
                    $('#taskstatus').removeClass(lastStatus).addClass('inprog');
                }


            }
        });

    });

    if($('.deletebutton')){
    $('.deletebutton').click(function(){
        jConfirm('ok','cancel','You are about to Delete this Task <br />Are you sure?', 'Confirm Delete', function(r){
            if(r==true)
            {
                $('.deleteform').submit();
            }
            if(r==false)
            {
                return false;
            }
        })
      });
    }

});