<?php
/**
 *  @param string $inputString Строка для кодирования.
 *  @return array Ассоциативный массив, содержащий закодированный результат (low, high) и таблицу частот.
 */
function arithmeticEncode(string $inputString): array
{
    // Определяем алфавит и частоты
    $leng = strlen($inputString);
    $alphabet = array_unique(str_split($inputString));
    $symbolFrequencies = array_fill_keys($alphabet, 0);
    foreach (str_split($inputString) as $symbol) {
        $symbolFrequencies[$symbol]++;
    }

    $totalFrequency = array_sum($symbolFrequencies);
    $symbolProbabilities = array_map(function ($frequency) use ($totalFrequency) {
        return $frequency / $totalFrequency;
    }, $symbolFrequencies);

    //Инициализация интервала
    $low = 0.0;
    $high = 1.0;

    //Кодирование каждого символа
    foreach (str_split($inputString) as $symbol) {
        $range = $high - $low;

        $newHigh = $low;
        foreach ($symbolProbabilities as $sym => $prob) {
            if ($sym === $symbol) {
                $newHigh += $range * $prob;
                break;
            }
            $newHigh += $range * $prob;
        }

        $newLow = $low;
        $counter = 0;
        foreach ($symbolProbabilities as $sym => $prob) {
            if ($counter >= array_search($symbol, array_keys($symbolProbabilities))) {
                break;
            }
            $newLow += $range * $prob;
            $counter++;
        }

        $low = $newLow;
        $high = $newHigh;
    }

    return ['low ' => $low, 'high' => $high, 'average' => ($low+$high) / 2, 'leng' => $leng, 'probabilities' => $symbolProbabilities];
}

if (isset($_FILES) && isset($_FILES["encode_file"]) && $_FILES["encode_file"]["error"] == UPLOAD_ERR_OK)
{
    $name = 'files/encode/' . $_FILES["encode_file"]["name"];
    move_uploaded_file($_FILES["encode_file"]["tmp_name"], $name);
    $path = pathinfo($name);
    $ext = mb_strtolower($path['extension']);
}

if (isset($ext)) {
    if (in_array($ext, array('jpeg', 'jpg', 'gif', 'png', 'webp', 'svg'))) {       
        if ($ext == 'svg') {
            $img = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($name));
        } else {
            $size = getimagesize($name);
            $img = 'data:' . $size['mime'] . ';base64,' . base64_encode(file_get_contents($name));
        }
    }
}
if (isset($img)) {
    $encodedResult = arithmeticEncode($img);
    $path = 'files/';
    $format = '.txt';
    a:
    $name = uniqid('', true);
    $full = $path . $name . $format;
    if (file_exists($full)) {
        goto a;
    } else {
        file_put_contents($full, json_encode($encodedResult));
    }
}
if (isset($_POST['encode_text'])) {
    $encodedResult = arithmeticEncode($_POST['encode_text']);
    $path = 'files/';
    $format = '.txt';
    b:
    $name = uniqid('', true);
    $full = $path . $name . $format;
    if (file_exists($full)) {
        goto b;
    } else {
        file_put_contents($full, json_encode($encodedResult));
    }
}
