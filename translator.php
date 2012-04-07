<?php
class Translator
{
  //private $db = __DIR__ . '/dict.db';
  private $db;
  private $words = Array();
  public function __construct()
  {
    $dbFile = __DIR__ . '/sqlite/dict.db';
    $this->db = new SQLite3($dbFile);
  }
  public function get($en)
  {
    $en = trim(strtolower($this->db->escapeString($en)));
    $results = $this->db->query("SELECT * FROM trans WHERE en = '{$en}'");
    $phons = Array();
    $pls = Array();
    while($res = $results->fetchArray(SQLITE3_ASSOC)) {
      $phons[] = $res['enPhon'];
      $pls[] = $res['pl'];
    }
    $allWords = Array(
        'en' => $en,
        'phons' => implode(' / ' , $phons), 
        'pls' => implode(' ', $pls));
    return $allWords;
  }
  public function loadFromFile()
  {
    $file = file(__DIR__ . '/sqlite/load.txt');
    $words = Array();
    foreach($file as $line) {
      $segm = explode("\t", $line);
      if (count($segm) == 3) {
        $words[strtolower(trim($segm[0]))] = Array(
            'fon' => $segm[1],
            'trans' => $segm[2],
        );
      }
    }
    foreach ($words as $en => $word) {
      //$this->db->query("insert into trans(en,enPhon,pl) values ('{$en}', '{$word['fon']}', '{$word['trans']}');");
    }
  }
  public function updatePl($en, $pl, $phon)
  {
    $en = trim(strtolower($en));
    $pl = trim($pl);
    $en = $this->db->escapeString($en);
    $pl = $this->db->escapeString($pl);
    $phon = $this->db->escapeString($phon);
    $existsQuery = "select pl from trans where en = '{$en}';";
    if (null === $this->db->querySingle($existsQuery)) {
      $this->db->query("insert into trans(en,pl,enPhon) values ('{$en}','{$pl}','{$phon}');");
    } else {
      $this->db->query("update trans set pl = '{$pl}', enPhon = '{$phon}' where en = '{$en}';");
    }
    if (null === ($result = $this->db->querySingle($existsQuery))) {
      throw new RuntimeException;
    }
    return $result;
  }
  public static function cleanTxt($txt)
  {
    $txt = strip_tags($txt);
    $txt = preg_replace("/({\d+}|\|)/", " ", $txt);
    return $txt;
  }
  public static function prepareForWordsExplode($txt)
  {
    $txt = preg_replace("/[<>\d:!?\.,=\/\(\)\s]+/", " ", $txt);
    $txt = preg_replace("/'(s|re|ve|m|ll) /", " ", $txt);
    $txt = str_replace("--", " ", $txt);
    //$txt = strtolower($txt);
    return $txt;
  }
}