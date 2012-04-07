function addToIgnore(word, poolId) {
  newval = $('#'+poolId).val() + ", " + word;
  $('#'+poolId).val(newval); 
}

function updateTrans(en, plNode, phonNode) {
  en = encodeURIComponent(en);
  pl = encodeURIComponent($('#' + plNode).val());
  phon = encodeURIComponent($('#' + phonNode).val());
  var url = 'trans.php?action=add&en=' + en + '&phon=' + phon + '&pl=' + pl;
    $.get(url, function(data) {
      $('#' + plNode).val(data);
    });
}

function getDef(word, plNode) {
  var url = 'trans.php?action=def&word=' + word;
    $.get(url, function(data) {
      $('#' + plNode).val(data);
    });
}
function getPhon(word, node) {
  var url = 'trans.php?action=phon&word=' + word;
    $.get(url, function(data) {
      $('#' + node).val(data);
    });
}