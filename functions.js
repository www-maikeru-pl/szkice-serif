function addToIgnore(word, poolId) {
  newval = $('#'+poolId).val() + ", " + word;
  $('#'+poolId).val(newval); 
}

function updateTrans(en, plNode) {
  en = encodeURIComponent(en);
  pl = encodeURIComponent($('#' + plNode).val());
  var url = 'trans.php?action=add&en=' + en + '&pl=' + pl;
    $.get(url, function(data) {
      $('#' + plNode).val(data);
    });
}
