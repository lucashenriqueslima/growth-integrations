<?php

namespace App\Helpers;

class ValidationHelper
{
    public static function cpfCnpj(string $cpfCnpj): bool
    {
        return self::cpf($cpfCnpj) || self::cnpj($cpfCnpj);
    }

    public static function cpf(string $cpf): bool
    {
        $cpfOnlyNumbers = FormatHelper::onlyNumbers($cpf);

        if (preg_match('/(\d)\1{10}/', $cpfOnlyNumbers)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpfOnlyNumbers[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpfOnlyNumbers[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    public static function cnpj(string $cnpj): bool
    {

        $cnpjOnlyNumbers = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpjOnlyNumbers) != 14) {
            return false;
        }

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpjOnlyNumbers)) {
            return false;
        }

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpjOnlyNumbers[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpjOnlyNumbers[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpjOnlyNumbers[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpjOnlyNumbers[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
