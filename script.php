<?php
drawBegin();
drawTextarea();

$txt = file_get_contents('sub.txt');
$txt = strip_tags($txt);
$lines = preg_split("/[\n]+/", $txt);
$txt = preg_replace("/[<>\d:!?\.,=\/]+/", " ", $txt);
$txt = str_replace("--", " ", $txt);
$sentences = Array();
$ignored = getIgnored();
foreach($lines as $line) {
  $lineTxt = preg_replace("/[<>\d:!?\.,=\/]+/", " ", $line);
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

$popularity = Array();
foreach($words as $word) {
  $word = trim($word);
  if (strlen($word) == 0 || in_array($word, $ignored)) {
    continue;
  }
  if (!isset($popularity[$word])) {
    $popularity[$word] = 0;
  }
  $popularity[$word]++;
}
natsort($popularity);
$popularity = array_reverse($popularity);
echo "<body style='line-height:200%;'>";
foreach($popularity as $word => $count) {
  addToIgnoredLink($word);
  echo "<a style='margin:10px; margin-top: 20px; font-size: 13pt;' "; 
  echo " href='http://dictionary.reference.com/browse/{$word}' target='_blank'>";
  echo "{$word}";
  echo "</a> ";
  if (isset($sentences[$word]) && is_array($sentences[$word]) ) {
    echo ": " . array_pop($sentences[$word]);
  }
  echo "<br />\n";
}
drawEnd();

function addToIgnoredLink($word)
{
  $word = htmlspecialchars($word, ENT_QUOTES);
  echo <<<EOT
 <button onclick='addToIgnore("{$word}", "ignoredpool");'>IGNORE</button>;
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
function drawTextarea()
{
$ignoredString = implode(", ", getIgnored());
echo "<h3>Ignored:</h3><textarea rows='10' cols='150' id='ignoredpool'>{$ignoredString}</textarea><hr />";
}
