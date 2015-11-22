<?php

include_once __DIR__."/../Polinom/Polinom.php";
include_once __DIR__."/../Channel/Message.php";

class Coder {

    private $polinomVector;
    private $polinomPower;

    private $errorPositions = [];

    public function __construct($strPolinom) {
        $polinom = new Polinom($strPolinom);
        $this->polinomVector = $polinom->getVector();
        $this->polinomPower = $polinom->getPower();

        $this->errorPositions["1001"] = 0;
        $this->errorPositions["1101"] = 1;
        $this->errorPositions["1111"] = 2;
        $this->errorPositions["1110"] = 3;
        $this->errorPositions["0111"] = 4;
        $this->errorPositions["1010"] = 5;
        $this->errorPositions["0101"] = 6;
        $this->errorPositions["1011"] = 7;
        $this->errorPositions["1100"] = 8;
        $this->errorPositions["0110"] = 9;
        $this->errorPositions["0011"] = 10;
    }

    public function getPower() {
        return $this->polinomPower;
    }

    public function encode(Message $message) {
        $encoded = [];
        for ($i = 0; $i < $message->length() + $this->polinomPower; $i++) {
            $encoded[$i] = false;
        }

        for ($i = 0; $i < $message->length(); $i++) {
            $encoded[$i] = ($encoded[$i] xor $message->getVector()[$i]);

            if ($encoded[$i]) {
                for ($j = 0; $j < $this->polinomPower + 1; $j++) {
                    $encoded[$i+$j] = ($encoded[$i+$j] xor $this->polinomVector[$j]);
                }
            }
        }

        $encoded = array_replace($encoded, $message->getVector());
        return new Message($encoded);
    }

    public function decode(Message $message) {
        $originalMessageLength = $message->length() - $this->polinomPower;

        $residue = $message->getVector();

        for ($i = 0; $i < $originalMessageLength; $i++) {
            if ($residue[$i]) {
                for ($j = 0; $j < $this->polinomPower + 1; $j++) {
                    $residue[$i + $j] = ($residue[$i + $j] xor ($this->polinomVector[$j]));
                }
            }
        }

        $syndrome = array_slice($residue, $originalMessageLength, $this->polinomPower);

        $decoded = array_slice($message->getVector(), 0, $originalMessageLength);

        if (isset($this->errorPositions[(new BitArray($syndrome))->getAsString()])) {
            $errorPosition = $this->errorPositions[(new BitArray($syndrome))->getAsString()];
            $decoded[$errorPosition] = !$decoded[$errorPosition];
        }

        return new Message($decoded);
    }
}