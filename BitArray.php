<?php


class BitArray {

    public $vector;

    public function __construct($vector) {

        if (is_array($vector)) {
            $this->vector = $vector;
            return;
        }

        $this->vector = [];
        for ($i = 0; $i < strlen($vector); $i++) {
            $this->vector[$i] = (substr($vector, $i, 1) == "1");
        }
    }

    public function length() {
        return count($this->vector);
    }

    public function getVector() {
        return $this->vector;
    }

    public function getAsString() {
        $str = "";
        foreach ($this->vector as $key => $value) {
            $str .= $value ? "1" : "0";
        }
        return $str;
    }

    public function equals(BitArray $obj) {
        return ($this->getAsString() == $obj->getAsString());
    }
}