<?php

error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);

include_once __DIR__."/Channel/Message.php";
include_once __DIR__."/Coder/Coder.php";
include_once __DIR__."/Channel/ErrorGenerator.php";
include_once __DIR__."/Channel/Channel.php";

define("INFORM_VECTOR", "00001010011");
define("POLINOM", "x4+x+1");

function fact($n) {
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

echo "| i | Cin | Nk | Ck |" . "\n";
echo "_____________________" . "\n";

for ($i = 1; $i < $encodedMessageSize + 1; $i++) {
    $correctErrorCount = 0;
    $errorMultiplicity = $i;

    $errorCount = fact($encodedMessageSize) / (fact($encodedMessageSize-$errorMultiplicity) * fact($errorMultiplicity));

    $errors = ErrorGenerator::generate($encodedMessageSize, $errorMultiplicity);

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

?>