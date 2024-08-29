<?php

namespace App\Helpers;

class FormatHelper
{
    public static function cpfOrCnpj(string $cpfOrCnpj): string
    {

        if (str_contains($cpfOrCnpj, '.')) {
            return $cpfOrCnpj;
        }

        if (strlen($cpfOrCnpj) === 11) {
            return substr($cpfOrCnpj, 0, 3) . '.' . substr($cpfOrCnpj, 3, 3) . '.' . substr($cpfOrCnpj, 6, 3) . '-' . substr($cpfOrCnpj, 9, 2);
        }

        return substr($cpfOrCnpj, 0, 2) . '.' . substr($cpfOrCnpj, 2, 3) . '.' . substr($cpfOrCnpj, 5, 3) . '/' . substr($cpfOrCnpj, 8, 4) . '-' . substr($cpfOrCnpj, 12, 2);
    }

    public static function phone(string $phone): string
    {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
    }

    public static function onlyNumbers(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}
