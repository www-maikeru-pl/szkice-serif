<?php
require_once 'translator.php';
require_once 'wordlist.php';
$translator = new Translator();

$txt = Translator::cleanTxt(file_get_contents_utf8('sub.txt'));
$lines = preg_split("/[\n]+/", $txt);
$txt = Translator::prepareForWordsExplode($txt);
$txt = str_replace("--", " ", $txt);
$sentences = Array();
$ignored = getIgnored();
$ignored = array_merge(getIgnored(), getLearned());
foreach($lines as $line) {
  $lineTxt = $txt = Translator::prepareForWordsExplode($line);
  $lineTxt = str_replace("--", " ", $lineTxt);
  $sentWords = preg_split("/[\s]+/", $lineTxt);
  foreach($sentWords as $sentWord) {
    $sentWord = trim($sentWord);
    if (strlen($sentWord) == 0) { continue; }
    if (!isset($sentences[$sentWord])) { $sentences[$sentWord] = Array(); }
    $sentences[$sentWord][strlen($line)] = $line;
  }
}
foreach($sentences as $key => $value) {
//  var_dump($sentences[$key]);
  ksort($sentences[$key]);
//  var_dump($sentences[$key]);
}
//var_dump($sentences);
$words = preg_split("/[\s]+/", $txt);
//var_dump($words);

function sortByPopularity($words, $ignored)
{
  $popularity = Array();
  foreach($words as $word) {
    $word = trim($word);
    if (strlen($word) == 0 || in_array($word, $ignored) || !preg_match('/[a-zA-Z]+/', $word)) {
      continue;
    }
    if (!isset($popularity[$word])) {
      $popularity[$word] = 0;
    }
    $popularity[$word]++;
  }
  $pop = Array();
  foreach($popularity as $word => $count) {
    if (!isset($pop[$count])) {
      $pop[$count] = array();
    }
    $pop[$count][] = $word;
  }
  krsort($pop);
  foreach ($pop as $count => $arrOfWords) {
    sort($pop[$count]);
  }
  return $pop;
  natsort($popularity);
  $popularity = array_reverse($popularity);
  return $popularity;
}
$popularity = sortByPopularity($words, $ignored);
$identifier = 0;

function printTrans($identifier, $translator, $word)
{
  $trans = $translator->get($word);
  $phon = $trans['phons'];
  $pl = transField($identifier, $word, $trans['pls']);
  echo "<td>{$phon}</td><td><strong>{$word}</strong></td><td>{$pl}</td>";
}

function printSentences($allSentences, $word, $print = true)
{
  if (!isset($allSentences[$word]) || !is_array($allSentences[$word])) {
      return;
  }
  $sentences = $allSentences[$word];
  $returnedSentences = Array();
  foreach($sentences as $key => $sentence) {
    $sentences[$key] = str_replace($word, '<strong>'.$word.'</strong>', $sentence);
  }
  $maxsentences = 3;
  $i = 0;
  while(count($sentences) > 0 && $i < $maxsentences) {
   $i++;
   if ($print) {
    echo "&bull; " . array_pop($sentences) . "<br />";
   } else {
     $returnedSentences[] = array_pop($sentences);
   }
  }
  return $returnedSentences;
}
function addToIgnoredLink($word)
{
  $word = htmlspecialchars($word, ENT_QUOTES);
  echo <<<EOT
 <button onclick='addToIgnore("{$word}", "ignoredpool");'>IGNORE</button>
EOT;

}
function drawBegin()
{
  echo <<<EOT
<html>
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <script type="text/javascript" src="jquery-1.7.2.min.js"></script>
   <script type="text/javascript" src="functions.js"></script>
  <head>
  <body>
EOT;
}

function drawEnd()
{
  echo <<<EOT
  </body>
</html>
EOT;
}

function getIgnored()
{
  $txt = file_get_contents('ignored.txt');
  $txt = strip_tags($txt);
  $ignored = preg_split("/[,]+/", $txt);
  $ignored = array_map('trim', $ignored);
  $ignored = array_unique($ignored);
  return $ignored;
}

function getLearned()
{
  $txt = file_get_contents('learned.txt');
  $txt = strip_tags($txt);
  $learned = preg_split("/[,]+/", $txt);
  $learned = array_map('trim', $learned);
  $learned = array_unique($learned);
  return $learned;
}
function drawTextarea()
{
$ignoredString = implode(", ", getIgnored());
echo "<h3>Ignored:</h3><textarea rows='10' cols='150' id='ignoredpool'>{$ignoredString}</textarea><hr />";
}

function returnLinks($word)
{
  $links = '';
  $links .= "<a style='margin:10px; margin-top: 20px; font-size: 13pt;' "; 
  $links .= " href='http://www2.getionary.pl/szukaj.html?q={$word}' target='_blank'>";
  $links .= "PL1";
  $links .= "</a> ";
  $links .= "<a style='margin:10px; margin-top: 20px; font-size: 13pt;' "; 
  $links .= " href='http://www.diki.pl/slownik-angielskiego/?q={$word}' target='_blank'>";
  $links .= "PL2";
  return $links;
}

function printWords($popularity, $sentences)
{
  foreach($popularity as $count => $words) {
    foreach($words as $word) {
      $links = returnLinks($word);
      $sent = implode("; ", printSentences($sentences, $word, false));
      echo <<<EOT
<tr><td>0</td><td>{$word}</td><td>{$sent}</td><td>{$links}</td><td></td><td>0</td><td>0</td><td>0</td></tr>
EOT;
    }
  }
}

function transField($fieldId, $en, $pl)
{
  $pl = htmlspecialchars($pl);
  return <<<EOT
  <textarea rows="4" cols="50" id="trans-{$fieldId}"/>{$pl}</textarea>
  <button onclick="updateTrans('{$en}', 'trans-{$fieldId}');">update translation</button>
EOT;
}

$allWords = Array();
foreach($popularity as $words) {
  foreach($words as $word) {
    $allWords[] = $word;
  }
}
$wordList = new WordList($allWords, $sentences, $translator);



function file_get_contents_utf8($fn) { 
     $content = file_get_contents($fn); 
      return mb_convert_encoding($content, 'UTF-8', 
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)); 
} 