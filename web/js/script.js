/* Author: */

$(document).ready(function() {


    //Fonts Scripts

    //'BABS' General Class
    Cufon.replace('.babs', {
        fontFamily: 'babs',
        hover: 'true'
    });
    //Homepage Section 02
    Cufon.replace('#page02 > h1', {
        textShadow: '0 -1px 1px #91a0b7',
        fontFamily: 'babs',
        hover: 'true'
    });
    //Homepage Section 03
    Cufon.replace('#page03 > h1', {
        textShadow: '0 -1px 1px #d0a582',
        fontFamily: 'babs',
        hover: 'true'
    });

    Cufon.replace('.topNote', {
        fontFamily: 'helv',
    });

    //'Helv' General Class
    Cufon.replace('.helv', {
        hover: 'true'
    });



    //Select Boxes Functions
    $(function () {
        $("#pos").selectbox();
        $("#startedDay").selectbox();
        $("#startedMonth").selectbox();
        $("#startedYear").selectbox();
        $("#endedDay").selectbox();
        $("#endedMonth").selectbox();
        $("#endedYear").selectbox();
        $("#urCat").selectbox();
        $("#signupState").selectbox();
        $("#signupGender").selectbox();
        $("#signupcountry").selectbox();
    });


    //Add & Remove Inputs
    $('#btnAdd').click(function() {
        var num     = $('.clonedInput').length;
        var newNum  = new Number(num + 1);

        var newElem = $('#input' + num).clone().attr('id', 'input' + newNum);

        newElem.children(':first').attr('id', 'name' + newNum).attr('name', 'name' + newNum);
        $('#input' + num).after(newElem);
        $('#btnDel').removeAttr('disabled');

        if (newNum == 5)
            $('#btnAdd').attr('disabled','disabled');
    });

    $('#btnDel').click(function() {
        var num = $('.clonedInput').length;

        $('#input' + num).remove();
        $('#btnAdd').removeAttr('disabled');

        if (num-1 == 1)
            $('#btnDel').attr('disabled','disabled');
    });

    $('#btnDel').attr('disabled','disabled');

    //width for Rectangle h1 Bar
    stuTitleBar = $('#main .wrapper').width();
    $('#student-01 > .wrapper h1 div').width(stuTitleBar-202);

    empTitleBar = $('#empSignup .wrapper').width();
    $('#empSignup > .wrapper h1 div').width(empTitleBar-216);
    //Function for Choosen >> page of inbox
    $(".chzn-select").chosen();
    $(".chzn-select-deselect").chosen({
        allow_single_deselect:true
    });

    setInterval(function(){

        //Loader Script
        $('#loading').width($('#loading').parent().width());
        $('#loading').height($('#loading').parent().height());
    }, 500);

    
});