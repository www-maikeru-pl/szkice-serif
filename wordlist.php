<?php
class Wordlist
{
  private $words;
  /**
   * @var Translator
   */
  private $translator;
  private $enWords;
  private $sentences;
  private $wordlist = Array();
  public function __construct(Array $words, Array $sentences, Translator $translator)
  {
    $brWords = file('/usr/share/dict/british-english', FILE_IGNORE_NEW_LINES);
    $amWords = file('/usr/share/dict/american-english', FILE_IGNORE_NEW_LINES);
    $this->enWords = array_merge($brWords, $amWords);
    $this->words = $words;
    $this->sentences = $sentences;
    $this->translator = $translator;
    $this->merge();
  }
  public function getList()
  {
    return $this->wordlist;
  }
  private function merge()
  {
    var_dump($this->enWords[2]);
    foreach($this->words as $word) {
      if (!in_array($word, $this->enWords)) {
        var_dump($word);
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
}