var oTable = null;
$(function(){
  $.getJSON('config.json', function(response){
    var
      table = $('#demo')
    ;

    Dtable = table.DataTable(response.config);
    yadcf.init(Dtable, response.yadcfConfig);
  });
});
