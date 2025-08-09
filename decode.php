<?php
/**
 * Декодирует данные, закодированные арифметическим кодированием.
 *
 * @param string $encodedData Закодированные данные в виде строки битов.
 * @param array $frequencies Ассоциативный массив частот символов (символ => частота).
 * @param int $dataLength Длина исходных данных (количество символов до кодирования).
 * @return string Декодированная строка.
 */
function arithmeticDecode(string $encodedData, array $frequencies, int $dataLength): string
{
    $low = 0;
    $high = 1;
    $range = 1;
    $decodedString = '';

    for ($i = 0; $i < $dataLength; $i++) {
        $range = $high - $low;
        $symbol = null;
        $cumulativeFrequency = 0;

        foreach ($frequencies as $char => $frequency) {
            $cumulativeFrequency += $frequency;
            $symbolHigh = $low + $range * $cumulativeFrequency / array_sum($frequencies);

            if ($encodedData < $symbolHigh) {
                $symbol = $char;
                $symbolLow = $low + $range * ($cumulativeFrequency - $frequencies[$char]) / array_sum($frequencies);
                break;
            }
        }

        if ($symbol === null) {
            throw new Exception("Не удалось декодировать символ.");
        }

        $decodedString .= $symbol;
        $high = $symbolHigh;
        $low = $symbolLow;
    }

    return $decodedString;
}

if (isset($_FILES) && isset($_FILES["decode_file"]) && $_FILES["decode_file"]["error"] == UPLOAD_ERR_OK)
{
    $name = 'files/decode/' . $_FILES["decode_file"]["name"];
    $pathd = pathinfo($name);
    $extd = mb_strtolower($pathd['extension']);
    if ($extd == 'txt') {
        move_uploaded_file($_FILES["decode_file"]["tmp_name"], $name);
        $file = file_get_contents($name);
        $file = json_decode($file, true);
    } else {
        $errors['decode_file'] = 'Изпользуйте текстовый формат файла';
    }
}

if (isset($file)) {
    $average = $file['average'];
    $leng = $file['leng'];
    $prob = $file['probabilities'];
    $file = arithmeticDecode($average, $prob, $leng);
    $path = 'files/';
    $name = uniqid('', true);
    $format = 'DECODE.txt';
    $full = $path . $name . $format;
    file_put_contents($full, $file);
}
?>