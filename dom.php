<?php
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
function getDef($word)
{
  $html = implode ('', file('http://www.diki.pl/slownik-angielskiego/?q='.$word));
  $dom = new Zend_Dom_Query($html);
  $results = $dom->query('ol.ms li span.hw');
  $defs = Array();
  foreach ($results as $result) {
      $defs[] = strip_tags($result->C14N());
  }
  return implode("\n", $defs);
}
