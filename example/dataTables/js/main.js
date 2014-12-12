var oTable = null;
$(function(){
  $.getJSON('config.json', function(response){
    var
      table = $('#demo'),
      dataTableConfig = response.config
    ;

    Dtable = table.DataTable(dataTableConfig);
    yadcf.init(Dtable, [
      {column_number : 0, filter_type: 'text'},
      {column_number : 1, filter_type: 'text'},
      {column_number : 2, filter_type: 'text'},
      {column_number : 3, filter_type: 'text'},
      {column_number : 4, filter_type: 'text'},
      {column_number : 5, filter_type: 'text'},
      {column_number : 6, filter_type: 'text'}
    ]);
  });
});
