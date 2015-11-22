<?php

include_once __DIR__."/Term.php";

class Polinom {

    private $terms = [];
    private $power;

    public function __construct($strPolinom) {
        $arr = preg_split("/\+/", preg_replace("/-/", "+-", $strPolinom));

        foreach ($arr as $key => $value) {
            $term = new Term(trim($value));
            $this->terms[$term->getPower()] = $term;
        }

        $this->power = max(array_keys($this->terms));
    }

    public function getPower() {
        return $this->power;
    }

    public function getVector() {
        $vector = [];
        for ($i = $this->power; $i > -1; $i--) {
            $vector[$this->power - $i] = array_key_exists($i, $this->terms);
        }
        return $vector;
    }

    public function toString() {
        $str = "";
        $vector = $this->getVector();
        foreach ($vector as $key => $value) {
            $str .= $value ? "1" : "0";
        }
        return $str;
    }
}