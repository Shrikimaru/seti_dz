<?php


class ErrorGenerator {

    public static function generate($length, $multiplicity) {
        $errors = [];
        for ($i = 0; $i < pow(2, $length); $i++) {
            if (self::getTrueCount($i) == $multiplicity) {
                $vector = self::toBits($i, $length);
                $errors[] = new Error($vector);
            }
        }

        return $errors;
    }

    public static function toBits($number, $length) {
        $bits = [];
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[$i] = ($number & (1 << $i)) != 0;
        }
        return $bits;
    }

    public static function getTrueCount($number) {
        $count = 0;
        for (; $number > 0; $number >>= 1) {
            if (($number & 1) == 1) {
                $count++;
            }
        }
        return $count;
    }
}