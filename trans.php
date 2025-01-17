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
  $phon = urldecode($_GET['phon']);
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
  echo $translator->updatePl($en, $cleanPl, $phon);
  return;
}
if ('def' === $action) {
  if (!isset($_GET['word'])) {
    throw new InvalidArgumentException;
  }
  require_once 'dom.php';
  echo getDef($_GET['word']);
}
if ('phon' === $action) {
  if (!isset($_GET['word'])) {
    throw new InvalidArgumentException;
  }
  require_once 'dom.php';
  echo getPhon($_GET['word']);
}