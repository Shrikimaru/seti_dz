<?php

include_once __DIR__."/../BitArray.php";

class Message extends BitArray {

    public function __construct($vector) {
        parent::__construct($vector);
    }
}