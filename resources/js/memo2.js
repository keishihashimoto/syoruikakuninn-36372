const { functionsIn, find } = require("lodash");

$(document).on("change", '#procedure-select', function(){
  var id = $('#procedure-select').val();
  if(id == 8){
    $('#nwpw').css("display", "block");
    $("#nwpw-ng").prop("checked", true)
    $("#nwpw").find("input").prop("disabled", false)
  } else {
    $('#nwpw').css("display", "none");
    $("#nwpw").find("input").prop("disabled", false)
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
    $("#payment-form-ng").prop("checked", true)
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
      $("#comer").css("display", "none")
      $('#comer').find("input[type='radio']:checked").prop("checked", false)
      $('#relation').css("display", "none")
      $('#relation').find("input[type='radio']:checked").prop("checked", false)
      $('#agent').css("display", "none")
      $('#agent').find("input[type='radio']:checked").prop("checked", false)
    }
    if(id <= 3){
      $('#agent').css("display", "none")
      $('#agent').find("input[type='radio']:checked").prop("checked", false)
    }
    if(id == 1){
      $('#previousContractor').css("display", "block")
      $("#self").prop("checked", true)
    }else{
      $("#previousContractor").css("display", "none")
      $("#previousContractor").find("input:checked").prop("checked", false)
      $("#previousContractorCome").css("display", "none")
      $("#previousContractorCome").find("input:checked").prop("checked", false)
    }
  })
  $(".comer").on("change", function(){
    var id = $('#procedure-select').val();
    var comer = $(".comer:checked").val();
    var sim = $("#sim").find("input:checked").val();
    if(comer == 1){
      $('#relation').css("display", "none")
      $('#relation').find("input[type='radio']:checked").prop("checked", false)
      $('#agent').css("display", "none")
      $('#agent').find("input[type='radio']:checked").prop("checked", false)
      $('#nwpw').find("input").prop("disabled", false)
      if(id == 8){
        $('#nwpw').css("display", "block")
        $("#nwpw-ng").prop("checked", true)
      }else{
        $('#nwpw').css("display", "none")
        $("#nwpw").find("input").prop("disabled", false)
        $('#nwpw').find("input[type='radio']:checked").prop("checked", false)
      }
    }else{
      $('#relation').css("display", "block")
      $("#sameAddress").prop("checked", true)
      if(id >= 4 && id <= 23){
        $('#agent').css("display", "block")
        $('#agentNot').prop("checked", true) 
      }
      if(id == 8 || ((id == 10 || id == 11) && sim == 2)){
        $('#nwpw').css("display", "block")
        $("#nwpw-ng").prop("checked", true)
        $('#nwpw').find("input").prop("disabled", true);
      }else{
        $('#nwpw').find("input").prop("disabled", false);
        $('#nwpw').css("display", "none")
        $('#nwpw').find("input[type='radio']:checked").prop("checked", false)
      }
    }
  })
  $('#procedure-select').on("change", function(){
    var id = $("#procedure-select").val()
    if(id == 10 || id == 11){
      $('#sim').css("display", "block")
      $("#sim-ok").prop("checked", true)
    }else{
      $('#sim').css("display", "none")
      $('#sim').find("input[type='radio']:checked").prop("checked", false)
    }
  })
  $('#sim').on("change", function(){
    var sim = $("#sim").find("input:checked").val();
    var comer = $(".comer:checked").val();
    if(sim == 2){
      $('#nwpw').css("display", "block");
      $("#nwpw").find("input").prop("disabled", false)
      $("#nwpw-ng").prop("checked", true)
      if(comer == 1){
        $("#nwpw").find("input").prop("disabled", false)
      }else if(comer == 2 ){
        $("#nwpw").find("input").prop("disabled", true)
      }
    }else{
      $('#nwpw').find("input").prop("disabled", false);
      $('#nwpw').css("display", "none");
      $("#nwpw").find("input[type='radio']:checked").prop("checked", false)
    }
  })
  $('#procedure-select').on("change", function(){
    var id = $("#procedure-select").val()
    if(id == 17){
      $("#compensation").css("display", "block")
      $('#go').prop("checked", true)
    }else{
      $('#nwpw').find("input").prop("disabled", false);
      $("#compensation").css("display", "none")
      $("#compensation").find("input[type='radio']:checked").prop("checked", false)
    }
  })
  $('#procedure-select').on("change", function(){
    var id = $('#procedure-select').val();
    if(id == 12){
      $("#ownDocomo").css("display", "block")
      $("#ownDocomoYes").prop("checked", true)
      $("#pointCardUser").css("display", "block")
      $("#identical").prop("checked", true)
    }else{
      $("#ownDocomo").css("display", "none")
      $("#ownDocomo").find("input[type='radio']:checked").prop("checked", false)
      $("#pointCardUser").find("input[type='radio']").prop("disabled", false)
      $("#pointCardUser").css("display", "none")
      $("#pointCardUser").find("input[type='radio']:checked").prop("checked", false)
    }
  })
  $("#previousContractor").on("change", function(){
    var previousContractor = $('#previousContractor').find("input:checked").val()
    if(previousContractor >= 2){
      $("#previousContractorCome").css("display", "block")
      $("#coming").prop("checked", true)
    }else{
      $("#previousContractorCome").css("display", "none")
      $("#previousContractorCome").find("input:checked").prop("checked", false) 
    }
  })
});