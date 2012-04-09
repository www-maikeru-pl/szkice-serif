<?php
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

$prefix = 'http://oald8.oxfordlearnersdictionaries.com/oxford3000/ox3k_';
$lists = Array(
  $prefix.'A-B/?page=1',
  $prefix.'A-B/?page=2',
  $prefix.'A-B/?page=3',
  $prefix.'A-B/?page=4',
  $prefix.'A-B/?page=5',
  $prefix.'C-D/?page=1',
  $prefix.'C-D/?page=2',
  $prefix.'C-D/?page=3',
  $prefix.'C-D/?page=4',
  $prefix.'C-D/?page=5',
  $prefix.'C-D/?page=6',
  $prefix.'E-G/?page=1',
  $prefix.'E-G/?page=2',
  $prefix.'E-G/?page=3',
  $prefix.'E-G/?page=4',
  $prefix.'E-G/?page=5',
  $prefix.'H-K/?page=1',
  $prefix.'H-K/?page=2',
  $prefix.'H-K/?page=3',
  $prefix.'L-N/?page=1',
  $prefix.'L-N/?page=2',
  $prefix.'L-N/?page=3',
  $prefix.'L-N/?page=4',
  $prefix.'O-P/?page=1',
  $prefix.'O-P/?page=2',
  $prefix.'O-P/?page=3',
  $prefix.'O-P/?page=4',
  $prefix.'Q-R/?page=1',
  $prefix.'Q-R/?page=2',
  $prefix.'Q-R/?page=3',
  $prefix.'S/?page=1',
  $prefix.'S/?page=2',
  $prefix.'S/?page=3',
  $prefix.'S/?page=4',
  $prefix.'T/?page=1',
  $prefix.'T/?page=2',
  $prefix.'U-Z/?page=1',
  $prefix.'U-Z/?page=2',
  $prefix.'U-Z/?page=3',
);

//print_r($lists);
$htmls = '';
foreach($lists as $line => $list) {
  $html = implode ('', file($list));
  $dom = new Zend_Dom_Query($html);
  $results = $dom->query('ul.wordlist-oxford3000');
  if (count($results) !== 1) {
    throw new \RuntimeException($list);
  }
  $htmls .= $results->current()->C14N();
  if ($line == 1) {
    //break;
  }
}
echo $htmls;




function getContent($html)
{

}