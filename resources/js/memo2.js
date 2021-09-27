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