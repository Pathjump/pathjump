$(document).ready(function() {
    //report user
    $('#reportThisUser').colorbox({
        href: reportUserUrl,
        iframe: true,
        innerWidth: 750,
        innerHeight: 250,
        scrolling: false,
        onClosed: function() {
            location.reload();
        }
    });

    $('.addToFavorite').click(function() {
        var status = $(this).attr('data-status');
        //show loading img
        $('.loading').show();
        $.ajax({
            url: addRemoveFavoriteUrl + "/" + status,
            success: function(msg) {
            },
            complete: function(msg) {
                location.reload();
            }
        });
    });

    //add inerest
    $('#addInerest').click(function() {
        jConfirm('ok', 'cancle', 'Are you sure you want to show this student interest', 'Show interest', function(r) {
            if (r == true) {
                //hide the link
                $('#addInerest').hide();
                //show loading img
                $('#addInerestImg').show();
                $.ajax({
                    url: addInterestUrl,
                    success: function(msg) {
                    },
                    complete: function(msg) {
                        location.reload();
                    }
                });
            }
        });
    });

    $('.validTo').datepicker({
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        minDate: new Date()
    });

    //ask for interview
//    $("#interviewAsk").fancybox({
//        'href'              : askForInterviewUrl,
//        'type'              : 'iframe',
//        'padding'           : 0,
//        'width'             : 850,
//        afterClose          : function() {
//        //            parent.location.reload(true);
//        }
//    });
//
//    //ask for hire
//    $("#companyHireUser").click(function(){
//       $.colorbox({
//           href:companyHireUserUrl
//       });
//    });

    //company hire user
    //    $('#companyHireUser').click(function(){
    //        jConfirm('ok','cancle','Are you sure you want to hire this user without interview', 'Hire user confirm', function (r) {
    //            if (r == true) {
    //                //hide the link
    //                $('#companyHireUser').hide();
    //                //show loading img
    //                $('#companyHireUserLoader').show();
    //                $.ajax({
    //                    url: companyHireUserUrl,
    //                    success: function(msg) {
    //                    },
    //                    complete: function(msg) {
    //                        location.reload();
    //                    }
    //                });
    //            }else{
    //                return false;
    //            }
    //        });
    //    });

    //company add question to user
    $('#companyAddQuestionButton').click(function() {
        //question text
        questionText = $('#companyAddQuestionText').val();
        if ($.trim(questionText)) {
            //hide the button
            $('#companyAddQuestionButton').hide();
            //show loading img
            $('.loading').show();
            $.ajax({
                url: addUserQuestionUrl + "/" + encodeURIComponent(questionText),
                success: function(msg) {
                },
                complete: function(msg) {
                    $('#companyAddQuestionText').val('');
                    location.reload();
                }
            });
        }
    });
});