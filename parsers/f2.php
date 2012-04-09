<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml"><head>

  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>
  <body>
<?php
const FIELDS = 5;
$outFile = __DIR__ . '/f2out.csv';
$handle = fopen("f2rand.csv", "r");
$i = 0;
while (($rowCsv = fgetcsv($handle, 0, "\t")) !== false) {
  $i++;
  if (count($rowCsv) !== FIELDS) {
    var_dump($rowCsv);
    throw new Exception('bad fields number');
  }
  $data[] = $rowCsv;
}
$substData = Array();
foreach($data as $row) {
  $substRow = Array();
  $substRow['def'] = $row[0];
  $substRow['qSent'] = $row[1];
  $substRow['word'] = $row[2];
  $substRow['phon'] = $row[3];
  $substRow['sent'] = $row[4];
  $substData[] = $substRow;
  //$out = "\n{$substRow['def']}\t{$substRow['qSent']}\t{$substRow['word']}\t{$substRow['phon']}\t{$substRow['sent']}";
  //file_put_contents($outFile, $out, FILE_APPEND);
}
$i = 0;
$questions = Array();
foreach($substData as $line) {
  if (trim(strlen($line['qSent'])) == 0) {
    continue;
  }
  if ($i++ > 10000000000000) {break;}
  $question = Array();
  $question['q'] = "{$line['qSent']} [{$line['def']}]";
  $question['word'] = $line['word'];
  $question['phon'] = $line['phon'];
  $sent = parseSent($line['qSent'], $line['sent']);
  $question['sent'] = $sent;
  $questions[] = $question;
}

saveToFile($questions, $outFile);


function saveToFile($questions, $filename) {
  file_put_contents($filename, ""); // clean the file
  foreach ($questions as $q) {
    $line = "{$q['q']}\t{$q['word']}\t{$q['phon']}\t{$q['sent']}";
    file_put_contents($filename, "\n".$line, FILE_APPEND);
  }
}




function printAll($questions) {
  foreach ($questions as $question) {
    echo "<hr />";
    echo "\n<br />&bull; {$question['q']}<br/>";
    echo "<strong>{$question['word']}</strong> <em>{$question['phon']}</em> <br />";
    echo "&bull; {$question['sent']}";
  }
}


function parseSent($q, $a)
{
  $q = str_replace('/', '\/', $q);
  $q = str_replace('\(', '\\\(', $q);
  $q = str_replace('(', '\(', $q);
  $q = str_replace('\)', '\\\)', $q);
  $q = str_replace(')', '\)', $q);
  $pattern = preg_replace('/~/', '(.*)', $q);
  $match = array();
  preg_match("/^$pattern$/", $a, $match);
  $highlightedA = $a;
  if (null !== array_shift($match)) {
    foreach($match as $part) {
      $highlightedA = str_replace($part, "<strong>$part</strong>", $highlightedA);
    }
  }
  return $highlightedA;
}
fclose($handle);
?>
</body>
</html>