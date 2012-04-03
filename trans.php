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
  echo $translator->updatePl($en, $pl);
  return;
}