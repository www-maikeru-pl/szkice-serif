<?php
$txt = file_get_contents('sub.txt');
$lines = preg_replace("/[\n]+/", " ", $txt);
$txt = preg_replace("/[<>\d:!?\.,=\/]+/", " ", $txt);
$txt = str_replace("--", " ", $txt);
$words = preg_split("/[\s]+/", $txt);
//var_dump($words);

$popularity = Array();
foreach($words as $word) {
  $word = trim($word);
  if (strlen($word) == 0) {
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
  echo "<a style='margin:10px; margin-top: 20px; font-size: 13pt;' "; 
  echo " href='http://dictionary.reference.com/browse/{$word}' target='_blank'>";
  echo "{$word}";
  echo "</a> ";
}
echo "</body>";
//var_dump($popularity);

