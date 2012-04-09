<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml"><head>

  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>
  <body>
<?php
const FIELDS = 4;
$outFile = __DIR__ . '/out.csv';
file_put_contents($outFile, "\n\n@@@@@@\n\n"); // clean the file
$handle = fopen("f1.csv", "r");
$i = 0;
while (($rowCsv = fgetcsv($handle, 0, "\t")) !== false) {
  $i++;
  //if ($i < 0) {continue;}
  if (count($rowCsv) !== FIELDS) {
    //var_dump($rowCsv);
    throw new Exception('bad fields number');
  }
  $data[] = $rowCsv;
  //if ($i > 500) {break;}
}
$substData = Array();
foreach($data as $row) {
  $substRow = Array();
  $substRow['def'] = $row[0];
  $substRow['sent'] = $row[1];
  $substRow['word'] = $row[2];
  $substRow['phon'] = $row[3];
  $substRow['qSent'] = Qsent::factory($substRow['word'], $substRow['sent'])->getQSent();
  $substData[] = $substRow;
  $out = "\n{$substRow['def']}\t{$substRow['qSent']}\t{$substRow['word']}\t{$substRow['phon']}\t{$substRow['sent']}";
  file_put_contents($outFile, $out, FILE_APPEND);
}
fclose($handle);

$wrong = 0;
$correct = 0;

foreach($substData as $line) {
  if (strlen($line['qSent'])) {
    $correct++;
  } else {
    $wrong++;
  }
}
var_dump($correct, $wrong);

class Qsent
{
  private $word;
  private $sent;
  private $qSent;
  /**
   * Do sth.
   * 
   * @param type $word
   * @param type $sent
   * @return QSent
   */
  public static function factory($word, $sent)
  {
    return new self($word, $sent);
  }
  private function __construct($word, $sent)
  {
    $this->word = trim(preg_replace('/\s+/', ' ', $word));
    $this->sent = trim(preg_replace('/\s+/', ' ', $sent));
    $this->qSent = $this->make();
  }
  private function separatePhrase($phrase)
  {
    //echo $phrase;
    //$phrase = str_replace('@@@', '', $phrase);
    $oldPhrase = $phrase;
    //$phrase = "it is accepted to be, have, etc. something";
    $phrase = str_replace('not be ', 'be not ', $phrase);
    $phrase = str_replace('etc.', '', $phrase);
    $phrase = preg_replace('/(be|have)[^a-zA-Z()]{1,3}(be|have)[^a-zA-Z()]{0,2}/', 'be/have', $phrase);
    //echo "$phrase\n";
    $phrase = preg_replace('/[\s]*\/[\s]*/', '/', $phrase);
    //echo "$phrase\n";
    $phrase = preg_replace('/[(][^()]*[)]/', ' ', $phrase);
    $phrase = preg_replace('/[(][^()]*[)]/', ' ', $phrase);
    $phrase = preg_replace('/[(][^()]*[)]/', ' ', $phrase);
    //echo "$phrase\n";
    $phrase = preg_replace('/[^a-zA-Z\'-\/]/', ' ', $phrase);
    if ( strpos($phrase, '(') !== false || strpos($phrase, ')') !== false) {
        var_dump($phrase);
        die();
    }
    //echo "$phrase\n";
    $phrase = preg_replace('/(^| )it($| )/', ' # ', $phrase);
    $phrase = preg_replace('/(^| )is($| )/', ' be ', $phrase);
    $phrase = str_replace(array('somebody', 'something', 'Somebody', 'Something'), '#', $phrase);
    $phrase = preg_replace('/(^| )(do )?(#|etc|sth|Sth)(\'s)*($| )/', ' # ', $phrase);
    //echo "$phrase\n";
    $phrase = preg_replace('/[#]+/', '#', $phrase);
    //echo "$phrase\n";
    $phrase = trim(preg_replace('/[\s]+/', ' ', $phrase));
    //echo "$phrase\n";
    $strPhrases = preg_split('/[\s]+/', $phrase, -1, PREG_SPLIT_NO_EMPTY);
    $phrases = Array();
    foreach($strPhrases as $phr) {
      $phrases[] = explode('/', $phr);
    }
    if ($oldPhrase !== $phrase) {
      //echo "\n------------\n$oldPhrase\n$phrase\n";
      //var_dump($phrases);
    }
    //die();
    //var_dump($phrase, $match);
    return $phrases;
  }
  public function getQSent()
  {
    return trim($this->qSent);
  }
  private function make()
  {
    $qSent = $this->substitute();
    if ($this->substSuccess($qSent)) {
      return $qSent;
    }
  }
  private function substSuccess($qSent)
  {
    return substr_count($qSent, '~') > 0;
  }
  private function substitute()
  {
    $isDebug = false;
    //$this->word = "act on/upon something";
    $phrases = $this->separatePhrase($this->word);
    $debug = Array();
    $sents = explode(' ', $this->sent);
    $debug['words'] = print_r($phrases, true);
    $debug['sents'] = print_r($sents, true);
    $qSents = Array();
    //echo "\n------------------\n";
    $curPhrases = array_shift($phrases);
    foreach($sents as $sent) {
      while(null !== $curPhrases && in_array('#', $curPhrases)) {
        $curPhrases = array_shift($phrases);
      }
      if (null === $curPhrases || count($curPhrases) == 0) {
        // jesli juz wyczerpaly sie slowa z hasla to do konca przepisanie
        $qSents[] = $sent;
        continue;
      }
      list($substituted, $newSent) = $this->trySubstitution($curPhrases, $sent);
      $qSents[] = $newSent;
      if ($substituted) {
        $curPhrases = array_shift($phrases);
      }
      while(null !== $curPhrases && in_array('#', $curPhrases)) {
        $curPhrases = array_shift($phrases);
      }
    }
    $debug['qSents'] = print_r($qSents, true);
    if ($isDebug && count($curPhrases)) {
      echo "\n=========================\n";
      print_r($debug);
      echo "\n=========================\n";
    }
    $fullQSents = implode(' ',$qSents);
    $fullQSents = preg_replace('/[\s]*[~][~ ]+[\s]*/', ' ~ ', $fullQSents);
    return $fullQSents;
  }
  private function trySubstitution($phrases, $sent)
  {
    $substs = Array();
    //echo "@@@@@@@@\n";
    $substituted = false;
    foreach($phrases as $phrase) {
      $sentParts = Array();
      preg_match('/^([^A-Za-z\'-]*)([A-Za-z\'-]*)([^A-Za-z\'-]*)$/', $sent, $sentParts);
      if (count($sentParts) == 4) {
        $subst = $sentParts[1] . $this->returnSubstitute($phrase, $sentParts[2]) . $sentParts[3];
      } else {
        $subst = $this->returnSubstitute($phrase, $sent);
      }
      if (strpos($subst, '~') !== false) {
        $substituted = true;
      } 
      $substs[] = $subst;
    }
    return array($substituted, $substs[0]);
  }
  private function returnSubstitute($word, $sent)
  {
    //var_dump('@@@@@@@@', $word, $sent);
    if (strtolower($word) === strtolower($sent)) {
      return "~";
    }
    $afterSent = $word;
    if (preg_match('/[a-zA-Z]self/', $word)) {
      $afterSent = preg_replace("/[a-zA-Z]sel(f|ves)/", '~', $sent);
    }
    if ($this->substSuccess($afterSent)) {
      return $afterSent;
    }
    if (preg_match('/^doing$/', $word)) {
      $afterSent = preg_replace("/^([a-zA-Z]+)ing$/", '$1~', $sent);
    }
    if ($this->substSuccess($afterSent)) {
      return $afterSent;
    }
    if (preg_match('/^be$/', $word)) {
      $afterSent = str_replace(array("'m", "'re", "'s", 'am', 'is', 'are', 'was', 'were', 'Am', 'Is', 'Are', 'Was', 'Were'), '~', $sent);
    }
    if (preg_match('/^(My|your|her|his|its|our|their)$/', $word)) {
      $afterSent = preg_replace('/(My|your|her|his|its|our|their)/', '~', $sent);
    }
    if ($this->substSuccess($afterSent)) {
      return $afterSent;
    }
    $tmpVerb = strtolower($word);
    if (strlen($tmpVerb) > 1) {
//      if ( strpos($tmpVerb, '(') !== false || strpos($tmpVerb, ')') !== false || strpos($tmpVerb, '/') !== false) {
//        var_dump($tmpVerb, $word, $sent);
//        die();
//      }
      $tmpVerbWithoutLastLetter = substr($tmpVerb, 0, -1);
      $tmpVerbLastLetter = substr($tmpVerb, -1);
      $afterSent = preg_replace("/^($tmpVerbWithoutLastLetter)($tmpVerbLastLetter)?(ied|ies|ed|d|ing|st|est|s)$/", '~', strtolower($sent));
    }
    if ($this->substSuccess($afterSent)) {
      return $afterSent;
    }
    if (strlen($word) > 1 && preg_match('/^be$/', $word)) {
      $afterSent = str_replace(array("'m", "'re", "'s", 'am', 'is', 'are', 'was', 'were', 'Am', 'Is', 'Are', 'Was', 'Were'), '~', $sent);
    }
    if ($this->substSuccess($afterSent)) {
      return $afterSent;
    }
    $afterSent = preg_replace("/^(.*)([^a-zA-Z]+)$word$/", '$1$2~', $sent);
    if ($this->substSuccess($afterSent)) {
      return $afterSent;
    }
//    $afterSent = preg_replace("/^$word([^a-zA-Z]+)(.*)$/", '~$1$2', $sent);
//    if ($this->substSuccess($afterSent)) {
//      return $afterSent;
//    }
    //var_dump($word . " | " . $sent . " | " . $afterSent);
    return $afterSent;
  }
}
?>
</body>
</html>