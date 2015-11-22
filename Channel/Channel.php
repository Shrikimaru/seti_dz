<?php

include_once __DIR__."/Error.php";
include_once __DIR__."/Message.php";
class Channel {
    public function __construct() {}

    public function transmit(Message $message, Error $errors) {
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