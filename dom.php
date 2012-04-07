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
function getPhon($word)
{
  $html = implode ('', file('http://oald8.oxfordlearnersdictionaries.com/dictionary/' . $word));
  $dom = new Zend_Dom_Query($html);
  $results = $dom->query('#entryContent .top-container span.i');
  $phons = Array();
  foreach ($results as $result) {
    $allTxt = $result->C14N();
    if (strpos($allTxt, 'pron-uk') !== false && strpos($allTxt, 'pronunciation') !== false ) {
      foreach($result->childNodes as $node) {
        if (XML_TEXT_NODE === $node->nodeType) {
          $phons[] = trim(strip_tags($node->textContent));
        }
      }
    }
  }
  $return = implode(" / ", $phons);
  return $return;
}