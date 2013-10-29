$(document).ready(function() {

    // Drob down menu styling
    $(function() {
        $(".select-box select").selectbox();
    });

    $(function() {
        $(".Choosen").selectbox();
    });

    // slide toggle
    $('.add-task-btn').click(function() {
        $('.Add-Task').slideToggle('slow');

    });

    $('.toggle-title').click(function() {
        $(this).next('.toggle-content').slideToggle(200);
        $(this).toggleClass("highlight");
    });

    // rows colors
    $('.Skils-box ul li:odd').css('background', '#f7f7f7');
    $('.Notification-list li:even').css('background', '#daeef8');


    // show hide / Drob down menu
    $('.Col-block .Search-form .more').click(function() {
        $('.Search-form').find('.select-box').show();
    });

});