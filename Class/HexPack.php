<?php

class hexPackUnpack
{
    public function packBinaryString($binaryString) {
        $length = strlen($binaryString);
        // считаем, сколько нужно добавить нулей, чтобы длина стала кратна 8
        $padLength = (8 - ($length % 8)) % 8;
        $binaryString .= str_repeat('0', $padLength);

        // сколько битов в последнем байте реально несут данные
        $validBitsInLastByte = ($length % 8) === 0 ? 8 : ($length % 8);

        // кодируем это число как один символ (байт)
        $header = chr($validBitsInLastByte);

        $result = $header;

        // пробегаем по бинарной строке по 8 бит и переводим в байты
        for ($i = 0; $i < strlen($binaryString); $i += 8) {
            $byte = substr($binaryString, $i, 8);
            $char = chr(bindec($byte)); // переводим 8-битный набор в десятичное число, потом в символ
            $result .= $char;
        }

        return $result;
    }

    public function unpackBinaryString($packedString) {
        if (strlen($packedString) === 0) return '';

        // первый байт — количество значимых битов в последнем байте
        $validBitsInLastByte = ord($packedString[0]);
        $binaryData = substr($packedString, 1);
        $result = '';

        for ($i = 0; $i < strlen($binaryData); $i++) {
            $byte = ord($binaryData[$i]);
            $bin = str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);

            // если это последний байт — обрезаем лишние нули
            if ($i === strlen($binaryData) - 1 && $validBitsInLastByte < 8) {
                $bin = substr($bin, 0, $validBitsInLastByte);
            }

            $result .= $bin;
        }

        return $result;
    }
}