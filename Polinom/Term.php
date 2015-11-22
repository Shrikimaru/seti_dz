<?php


class Term {

    public static $variable = "x";
    private $coeff;
    private $power;

    public function __construct($strTerm) {
        $parts = preg_split("/".self::$variable."/", $strTerm);

        if (($parts[0] == "") && ($parts[1] == "")) {
            $this->coeff = 1;
            $this->power = 1;
        } elseif (count($parts) == 1) {
            $this->power = 0;
            $this->coeff = $parts[0];
        } else {
            $this->coeff = strlen($parts[0]) > 0 ? $parts[0] : 1;
            $this->power = strlen($parts[1]) > 0 ? $parts[1] : 0;
        }
    }

    public function getPower() {
        return $this->power;
    }

    public function compareTo(Term $o) {
        return $this->power == $o->getPower();
    }
}