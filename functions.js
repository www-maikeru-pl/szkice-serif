function addToIgnore(word, poolId) {
  newval = $('#'+poolId).val() + ", " + word;
  $('#'+poolId).val(newval); 
}