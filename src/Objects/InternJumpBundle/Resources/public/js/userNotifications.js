$(document).ready(function(){
    $(".chzn-select").chosen();

    setInterval(function(){
        //Loader Script
        $('#loading').width($('#loading').parent().width());
        $('#loading').height($('#loading').parent().height());
    }, 500);

    //notification action on click
    $('li.singleRow').click(function(){
        thisDiv = $(this);
        var notificationId = thisDiv.attr('notificationId');
        var notificationPage = thisDiv.attr('notificationPage');
        //check if this notification new
        if(thisDiv.hasClass('focus')){
            //show the loading image
            $('.loading').show();
            $.ajax({
                url: notficationMarkUrl+'/'+notificationId,
                success: function(msg) {
                },
                complete: function(msg) {
                    //go to notification page
                    window.location = notificationPage;
                }
            });
        }else{
            //go to notification page
            window.location = notificationPage;
        }
    });

    //mark all notifications as read
    $('.markAllUnreadNotification').click(function(){
        thisLink = $(this);
        //hide the link
        thisLink.hide();
        //show the loading image
        $('#loading').show();
        $.ajax({
            url: notficationMarkAllUrl,
            success: function(msg) {
            },
            complete: function(msg) {
                window.location.reload();
            }
        });
    });
});