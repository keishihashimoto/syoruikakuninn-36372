const { functionsIn } = require("lodash");

$(document).on("change", '#procedure-select', function(){
  var id = $('#procedure-select').val();
  if(id == 8){
    $('#nwpw').css("display", "block");
  } else {
    $('#nwpw').css("display", "none");
  }
});

$(document).on("change", '#procedure-select', function(){
  var id = $('#procedure-select').val();
  if(id == 4){
    $('#loan').css("display", "block");
  } else {
    $('#loan').css("display", "none");
  }
});

$(document).on("change", "#procedure-select", function(){
  var id = $('#procedure-select').val();
  if(id == 13){
    $('#payment-form').css("display", "block");
  } else {
    $("#payment-form").css("display", "none");
  }
})

$(document).on("change", "#procedure-select", function(){
  var id = $('#procedure-select').val();
  if(id == 21 || id == 22 || id == 23){
    $('#student').css("display", "block");
  } else {
    $('#student').css("display", "none");
  }
});


jQuery(function($){
  $('#procedure-select').on("change", function(){
    var id = $('#procedure-select').val();
    console.log(id);
    if(id < 13 || id > 15){
      $("#comer").css("display", "block");
    }else{
      $("#comer").css("display", "none");
    }
  })
  $(".comer").on("change", function(){
    var comer = $(".comer:checked").val()
    if(comer == 1){
      $('#relation').css("display", "none")
      $('#agent').css("display", "none")
    }else{
      $('#relation').css("display", "block")
      $('#agent').css("display", "block")
    }
  })
});