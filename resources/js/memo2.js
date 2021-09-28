const { functionsIn } = require("lodash");

$(document).on("change", '#procedure-select', function(){
  var id = $('#procedure-select').val();
  if(id == 8){
    $('#nwpw').css("display", "block");
    $("#nwpw-ok").prop("checked", true)
  } else {
    $('#nwpw').css("display", "none");
    $("#nwpw").find("input[type='input']:checked").prop("checked", false)
  }
});

$(document).on("change", '#procedure-select', function(){
  var id = $('#procedure-select').val();
  if(id == 4){
    $('#loan').css("display", "block");
    $('#loan-on').prop("checked", true)
  } else {
    $('#loan').css("display", "none");
    $('#loan').find("input[type='radio']:checked").prop("checked", false)
  }
});

$(document).on("change", "#procedure-select", function(){
  var id = $('#procedure-select').val();
  if(id == 13){
    $('#payment-form').css("display", "block");
    $("#payment-form-ok").prop("checked", true)
  } else {
    $("#payment-form").css("display", "none");
    $('#payment-form').find('#input[type="radio"]:checked').prop("checked", false)
  }
})

$(document).on("change", "#procedure-select", function(){
  var id = $('#procedure-select').val();
  if(id == 21 || id == 22 || id == 23){
    $('#student').css("display", "block");
    $('#not_student').prop("checked", true)
  } else {
    $('#student').css("display", "none");
    $("#student").find('input[type="radio"]:checked').prop("checked", false)
  }
});


jQuery(function($){
  $('#procedure-select').on("change", function(){
    var id = $('#procedure-select').val();
    if(id < 13 || id > 15){
      $("#comer").css("display", "block");
      $("#comer-self").prop("checked", true)
    }else{
      $("#comer").css("display", "none");
      $('#comer').find("input[type='radio']:checked").prop("checked", false)
      $('#relation').css("display", "none")
      $('#relation').find("input[type='radio']:checked").prop("checked", false)
      $('#agent').css("display", "none")
      $('#agent').find("input[type='radio']:checked").prop("checked", false)
    }
  })
  $(".comer").on("change", function(){
    var comer = $(".comer:checked").val()
    if(comer == 1){
      $('#relation').css("display", "none")
      $('#relation').find("input[type='radio']:checked").prop("checked", false)
      $('#agent').css("display", "none")
      $('#agent').find("input[type='radio']:checked").prop("checked", false)
    }else{
      $('#relation').css("display", "block")
      $("#sameAddress").prop("checked", true)
      $('#agent').css("display", "block")
      $('#agentNot').prop("checked", true)
    }
  })
  $('')
});