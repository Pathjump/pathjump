$(document).ready(function() {
    $(".select-box select").livequery(function() {
        $(this).selectbox();
    });

    $('input[type="checkbox"]').livequery(function() {
        $(this).ezMark();
    });

    $('#readAllMessages').livequery(function() {
        $(this).on('click', function() {
            confirmSubmit('readAllMessages');
        });
    });
    $('#deleteAllMessages').livequery(function() {
        $(this).on('click', function() {
            confirmSubmit('deleteAllMessages');
        });
    });
});

function changeMessagesSelection() {
    $('.companyMessage').click();
}

function requestData(urlToRequest, divId, completeFunction) {
    $('.loading').show();
    $.ajax({
        url: urlToRequest,
        success: function(msg) {
            $(divId).html(msg);
        },
        complete: function() {
            $('.loading').hide();
            if ($.isFunction(completeFunction)) {
                completeFunction();
            }
        }
    });
}

function getTabContents(tabIndex) {
    var urlToRequest = '';
    var divId = '';
    //specify the url to use
    switch (tabIndex) {
        case 1:
            urlToRequest = companyInboxurl;
            divId = '#tab1';
            break;
        case 2:
            urlToRequest = companyOutboxurl;
            divId = '#tab2';
            break;
        default:
            urlToRequest = companyMessageurl;
            divId = '#tab3';
    }
    requestData(urlToRequest, divId);
}

function confirmSubmit(attributToAdd) {
    if ($('.companyMessage:checked').length > 0) {
        if (attributToAdd === 'deleteAllMessages') {
            jConfirm(
                    'ok',
                    'cancel',
                    'Are you sure you want to Delete ' + $('.companyMessage:checked').length + ' messages',
                    'Warning',
                    function(confirm) {
                        if (confirm) {
                            submitMessagesForm('deleteAllMessages');
                        }
                    }
            );
        } else {
            submitMessagesForm('readAllMessages');
        }
    }
}

function submitMessagesForm(attributToAdd) {
    $('.loading').show();
    $.ajax({
        url: $('#companyMessagesForm').attr('action'),
        type: $('#companyMessagesForm').attr('method'),
        data: $('#companyMessagesForm').serialize() + '&' + attributToAdd + '=1',
        complete: function() {
            if ($('#currentBox').val() === 'outbox') {
                window.location = outboxUrl;
            } else {
                window.location = inboxUrl;
            }
        }
    });
}

function confirmDelete(form, url, divId) {
    jConfirm(
            'ok',
            'cancel',
            'Are you sure you want to Delete This Message',
            'Warning',
            function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: $(form).attr('action'),
                        type: $(form).attr('method'),
                        data: $(form).serialize(),
                        complete: function() {
                            requestData(url, divId);
                        }
                    });
                }
            }
    );
}