jQuery(function($){
  $(".delete-memo").on("click", function(e){
    
    
    e.preventDefault()
    
    var memo_id = $(this).parents('tr').find(".memo-name").attr("id")
    $.ajax({
      headers: {
        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
      },
      url: `/memos/${memo_id}`,
      type: "POST",
      data: {"id": memo_id, "_method": "DELETE"},
    })

    .done(function(){
      console.log($(`div[id="${memo_id}"]`).parents("tr"))
      $(`div[id="${memo_id}"]`).parents("tr").remove()
    })

    .fail(function(){
      alert("予期せぬエラーが発生しました。ページのリロードをお試しください。")
    })
  })
})