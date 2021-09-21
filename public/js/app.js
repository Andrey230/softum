
$(document).on('click','.remove_database',function (){
   let data = {
      "name" : $(this).data('name')
   };
   let parentElement = $(this).parent();
   $.ajax({
      url: "/databases/remove",
      method: "DELETE",
      dataType: 'json',
      data: JSON.stringify(data)
   }).done(function(data) {
      parentElement.remove();
   });
});