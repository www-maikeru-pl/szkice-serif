<?php
class Wordlist
{
  private $words;
  /**
   * @var Translator
   */
  private $translator;
  private $whitelist;
  private $ignored;
  private $sentences;
  private $wordlist = Array();
  public function __construct(Array $words, Array $sentences, Translator $translator)
  {
    $whitelist1 = file(__DIR__ . '/top100k.txt', FILE_IGNORE_NEW_LINES);
    $whitelist2 = file(__DIR__ . '/aspell.txt', FILE_IGNORE_NEW_LINES);
    $whitelist3 = file(__DIR__ . '/addictional.txt', FILE_IGNORE_NEW_LINES);
    $this->whitelist = array_merge($whitelist1, $whitelist2, $whitelist3);
    $this->ignored = $this->getIgnored(__DIR__ . '/ignored.txt');
    $this->words = $words;
    $this->sentences = $sentences;
    $this->translator = $translator;
    $this->merge();
  }
  public function getList($wordAsKey = false)
  {
    if ($wordAsKey) {
      $wordArray = Array();
      foreach($this->wordlist as $values) {
        $wordArray[$values['en']] = $values;
      }
      return $wordArray;
    }
    return $this->wordlist;
  }
  private function merge()
  {
    $wordsToAdd = Array();
    foreach($this->words as $word) {
      if (in_array($word, $this->ignored)) {
        continue;
      }
      if (!in_array(strtolower($word), $this->whitelist )) {
        //var_dump($word);
        continue;
      }
      $trans = $this->translator->get($word);
      $trans['sent'] = implode(' ', $this->printSentences($word));
      $this->wordlist[] = $trans;
    }
  }
  private function printSentences($word, $format = true)
  {
    if (!isset($this->sentences[$word]) || !is_array($this->sentences[$word])) {
        return array(0 => '');
    }
    $sentences = $this->sentences[$word];
    $returnedSentences = Array();
    foreach($sentences as $key => $sentence) {
      $sentences[$key] = str_replace($word, '<strong>'.$word.'</strong>', $sentence);
    }
    $maxsentences = 3;
    $i = 0;
    while(count($sentences) > 0 && $i < $maxsentences) {
    $i++;
    if ($format) {
      if (!isset($returnedSentences[0])) {
        $returnedSentences[0] = '';
      }
      $returnedSentences[0] .= "&bull; " . array_pop($sentences) . "<br />";
    } else {
      $returnedSentences[] = array_pop($sentences);
    }
    }
    return $returnedSentences;
  }
  private function getIgnored($file)
  {
    $txt = file_get_contents($file);
    $txt = strip_tags($txt);
    $ignored = preg_split("/[,]+/", $txt);
    $ignored = array_map('trim', $ignored);
    $ignored = array_unique($ignored);
    return $ignored;
  }
}