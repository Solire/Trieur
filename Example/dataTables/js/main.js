var oTable = null;
$(function(){
  $.fn.datepicker.defaults.language = 'fr';
  $.fn.datepicker.defaults.autoclose = true;
  $.fn.datepicker.defaults.clearBtn = true;

  $.getJSON('config.json', function(response){
    var table = $('#demo');

    Dtable = table.DataTable(response.config);
    new $.fn.dataTable.ColumnFilter(Dtable, response.columnFilterConfig);
  });
});
