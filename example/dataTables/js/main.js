var oTable = null;
$(function(){
  $.getJSON('config.json', function(response){
    var
      table = $('#demo'),
      dataTableConfig = response.config
    ;

    Dtable = table.DataTable(dataTableConfig);
  });
});
