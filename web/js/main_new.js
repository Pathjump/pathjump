$(document).ready(function(){
  if($('.bxslider').length > 0){
  $('.bxslider').bxSlider({responsive: true, useCSS: false,mode :'fade',pager:false ,controls:false ,captions:false,auto:true});
  }
  if($('#myCarouse3').length > 0){
    $('#myCarouse3').carousel({
    interval: 2000
    });
  }
});