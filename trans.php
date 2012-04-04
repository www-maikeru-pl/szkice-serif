<?php
require_once 'translator.php';
$translator = new Translator;
//$translator->loadFromFile();
//var_dump($translator->get('villages'));
if (!isset($_GET['action'])) {
  return;
}
$action = $_GET['action'];
if ('add' === $action) {
  if (!isset($_GET['en']) || !isset($_GET['pl'])) {
    throw new InvalidArgumentException;
  }
  $en = urldecode($_GET['en']);
  $pl = urldecode($_GET['pl']);
  $cleanPlArr = array();
  foreach(explode("\n", $pl) as $line) {
    $line = str_replace('Dodaj do powtórek w eTutor', '', $line);
    $line = preg_replace('/^\s*[#]\s*/', '', $line);
    $line = trim($line);
    if (strlen($line) > 0) {
      $cleanPlArr[] =  $line;
    }
  }
  $cleanPl = implode("\n", $cleanPlArr);
  echo $translator->updatePl($en, $cleanPl);
  return;
}