<?php

namespace main;

error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);

define("INFORM_VECTOR", "00001010011");
define("POLINOM", "x4+x+1");

class ArrayOfBits
{

    public $vector;

    public function __construct($vector)
    {
        if (is_array($vector)) {
            $this->vector = $vector;
            return;
        }
        $this->vector = [];
        for ($i = 0; $i < strlen($vector); $i++) {
            $this->vector[$i] = (substr($vector, $i, 1) == "1");
        }
    }

    public function length()
    {
        return count($this->vector);
    }

    public function getVector()
    {
        return $this->vector;
    }

    public function getAsString()
    {
        $str = "";
        foreach ($this->vector as $key => $value) {
            $str .= $value ? "1" : "0";
        }
        return $str;
    }

    public function equals(ArrayOfBits $obj)
    {
        return ($this->getAsString() == $obj->getAsString());
    }
}

class Term
{

    public static $variable = "x";
    private $coeff;
    private $power;

    public function __construct($strTerm)
    {
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

    public function getPower()
    {
        return $this->power;
    }

    public function compareTo(Term $o)
    {
        return $this->power == $o->getPower();
    }
}

class Polinom
{

    private $terms = [];
    private $power;

    public function __construct($strPolinom)
    {
        $arr = preg_split("/\+/", preg_replace("/-/", "+-", $strPolinom));
        foreach ($arr as $key => $value) {
            $term = new Term(trim($value));
            $this->terms[$term->getPower()] = $term;
        }
        $this->power = max(array_keys($this->terms));
    }

    public function getPower()
    {
        return $this->power;
    }

    public function getVector()
    {
        $vector = [];
        for ($i = $this->power; $i > -1; $i--) {
            $vector[$this->power - $i] = array_key_exists($i, $this->terms);
        }
        return $vector;
    }

    public function toString()
    {
        $str = "";
        $vector = $this->getVector();
        foreach ($vector as $key => $value) {
            $str .= $value ? "1" : "0";
        }
        return $str;
    }
}

class Coder
{

    private $polinomVector;
    private $polinomPower;

    public function getPolinomVector()
    {
        return $this->polinomVector;
    }

    public function __construct($strPolinom)
    {
        $polinom = new Polinom($strPolinom);
        $this->polinomVector = $polinom->getVector();
        $this->polinomPower = $polinom->getPower();
    }

    public function getPower()
    {
        return $this->polinomPower;
    }

    public function encode(Message $message)
    {
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

    public function decode(Message $message)
    {
        $encodedMessageLength = $message->length();
        $originalMessageLength = $encodedMessageLength - $this->polinomPower;
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

        // Исправление ошибки
        for ($i = 0; $i < $originalMessageLength; $i++) {

            $errorVector = [];
            for ($p = 0; $p < $encodedMessageLength; $p++) {
                $errorVector[$p] = 0;
            }
            $errorVector[$i] = 1;

            for ($j = 0; $j < $originalMessageLength; $j++) {
                if ($errorVector[$j]) {
                    for ($k = 0; $k < $this->polinomPower + 1; $k++) {
                        $errorVector[$j + $k] = ($errorVector[$j + $k] xor ($this->polinomVector[$k]));
                    }
                }
            }

            $syndromeTest = array_slice($errorVector, $originalMessageLength, $this->polinomPower);

            if ((new ArrayOfBits($syndrome))->getAsString() == (new ArrayOfBits($syndromeTest))->getAsString()) {
                $errorPosition = $i;
                $decoded[$errorPosition] = !$decoded[$errorPosition];
            }
        }
        return new Message($decoded);
    }
}

class Channel
{
    public function __construct()
    {

    }

    public function transmit(Message $message, Error $errors)
    {
        if ($message->length() != $errors->length()) {
            throw new Exception("Вектор сообщения и ошибки должны быть одной длины");
        }
        $receivedMessage = $message->getVector();
        for ($i = 0; $i < $message->length(); $i++) {
            $receivedMessage[$i] = ($receivedMessage[$i] xor $errors->getVector()[$i]);
        }
        return new Message($receivedMessage);
    }
}

class Error extends ArrayOfBits
{
    public function __construct($vector)
    {
        parent::__construct($vector);
    }
}

class Message extends ArrayOfBits
{
    public function __construct($vector)
    {
        parent::__construct($vector);
    }
}

class GeneratorOfErrors
{
    public static function generate($length, $multiplicity)
    {
        $errors = [];
        for ($i = 0; $i < pow(2, $length); $i++) {
            if (self::getTrueCount($i) == $multiplicity) {
                $vector = self::toBits($i, $length);
                $errors[] = new Error($vector);
            }
        }

        return $errors;
    }

    public static function toBits($number, $length)
    {
        $bits = [];
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[$i] = ($number & (1 << $i)) != 0;
        }
        return $bits;
    }

    public static function getTrueCount($number)
    {
        $count = 0;
        for (; $number > 0; $number >>= 1) {
            if (($number & 1) == 1) {
                $count++;
            }
        }
        return $count;
    }
}

function fact($n)
{
    $fact = 1;
    for ($i = 1; $i <= $n; $i++) {
        $fact *= $i;
    }
    return $fact;
}

$message = new Message(INFORM_VECTOR);
$coder = new Coder(POLINOM);
$channel = new Channel();
$encoded = $coder->encode($message);
$encodedMessageSize = $message->length() + $coder->getPower();

$numOfErrorsDetected = [];

echo "| i | Cin | Nk | Ck |" . "\n";
echo "__________________________" . "\n";

for ($i = 1; $i < $encodedMessageSize + 1; $i++) {
    $correctErrorCount = 0;
    $errorMultiplicity = $i;
    $errorCount = fact($encodedMessageSize) / (fact($encodedMessageSize-$errorMultiplicity) * fact($errorMultiplicity));
    $errors = GeneratorOfErrors::generate($encodedMessageSize, $errorMultiplicity);

    foreach ($errors as $key => $error) {
        $response = $channel->transmit($encoded, $error);
        $decoded = $coder->decode($response);
        if ($message->equals($decoded)) {
            $correctErrorCount++;
        }
    }
    $correctionCoefficient = $correctErrorCount / count($errors);

    echo
        "| ". $errorMultiplicity . " |" .
        " " . $errorCount . " |" .
        " " . $correctErrorCount . " |" .
        " " . $correctionCoefficient . " |\n";
}