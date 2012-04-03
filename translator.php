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
  public function updatePl($en, $pl)
  {
    $en = trim(strtolower($en));
    $pl = trim($pl);
    $en = $this->db->escapeString($en);
    $pl = $this->db->escapeString($pl);
    $existsQuery = "select pl from trans where en = '{$en}';";
    if (null === $this->db->querySingle($existsQuery)) {
      $this->db->query("insert into trans(en,pl) values ('{$en}','{$pl}');");
    } else {
      $this->db->query("update trans set pl = '{$pl}' where en = '{$en}';");
    }
    if (null === ($result = $this->db->querySingle($existsQuery))) {
      throw new RuntimeException;
    }
    return $result;
  }
}