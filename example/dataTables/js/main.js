var oTable = null;
$(function(){
  $.getJSON('config.json', function(response){
    var
      table = $('#demo'),
      thead = $('<thead>').appendTo(table),
      tfoot = $('<tfoot>').appendTo(table),
      tbody = $('<tbody>').appendTo(table),
      dataTableConfig = response.config;

//    $.each(dataTableConfig.columns, function(ii, col){
//      var
//        thH = $('<th>').html(col.title),
//        thF = thH.clone(false);
//
//      thH.appendTo(thead);
//      thF.appendTo(tfoot);
//    });

    oTable = table.dataTable(dataTableConfig);
  });
});
