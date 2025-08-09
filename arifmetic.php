<?php

require_once('Class\Encode.php');
require_once('Class\Decode.php');
require_once('Class\HexPack.php');

$flag = false;
if (isset($_POST['txt'])) {
    $flag = true;
}

$coder = new AdaptiveArithmeticCoder(); // класс кодирует данные
$pack = new hexPackUnpack(); // класс занимается упаковкой и распаковкой битов

if ($flag) {
    //вариант с сохранением в .txt (весит больше)
    if (isset($_POST['encode_text'])) {
        $original = $_POST['encode_text'];
        $alphabet = implode(array_unique(mb_str_split($original))); // разбираем текст на уникальные символы
        $leng = strlen($original); // длина текста
        $data = $coder->encode($original);
        $encodedPack = $pack->packBinaryString($data); // упаковываем биты в байты функцией chr
    
        $jsonArr['code'] = base64_encode($encodedPack); //кодируем байты в символы base64 (!!!это не оптимальный вариант, он увеличит вес на 33%!!!)
        $jsonArr['alphabet'] = $alphabet; //уникальные символы
        $jsonArr['leng'] = $leng; //длина текста
        $jsonData = json_encode($jsonArr); //переводим в формат json
    
        $path = 'files/';
        $format = '.txt';
        b:
        $name = uniqid('', true);
        $full = $path . $name . $format;
        if (file_exists($full)) {
            goto b;
        } else {
            file_put_contents($full, $jsonData);
        }
    }
    // сохраняем файл пользователя
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
    // декодируем из файла
    if (isset($file)) {
        $code = $file['code'];
        $leng = $file['leng'];
        $alphabet = $file['alphabet'];
    
        $decoder = new AdaptiveArithmeticDecoder();
    
        $data = base64_decode($code); // декодируем из base64 в байты
        $decodedPack = $pack->unpackBinaryString($data); // декодируем байты в биты
        $decoded = $decoder->decode($decodedPack, $alphabet, $leng); // декодируем сам текст
    
        $path = 'files/';
        $name = uniqid('', true);
        $format = 'DECODE.txt';
        $full = $path . $name . $format;
        file_put_contents($full, $decoded);
    }
} else {
    //вариант с сохранением в .bin
    if (isset($_POST['encode_text'])) {
        $original = $_POST['encode_text'];
        $alphabetArr = array_unique(mb_str_split($original));
        $alphabet = implode($alphabetArr);
        $leng = strlen($original);
    
        $data = $coder->encode($original);
        $encodedPack = $pack->packBinaryString($data); // 1 байт паддинга + байты
    
        // Формируем бинарный файл
        $bin = '';
        $bin .= chr(strlen($alphabet));             // 1 байт: длина алфавита
        $bin .= $alphabet;                          // N байт: алфавит
        $bin .= pack('N', $leng);                   // 4 байта: длина текста (32-бит)
        $bin .= $encodedPack;                       // данные (уже с 1 байтом паддинга внутри)
    
        // Сохраняем в файл
        $path = 'files/';
        $format = '.bin';
        do {
            $name = uniqid('', true);
            $full = $path . $name . $format;
        } while (file_exists($full));
    
        file_put_contents($full, $bin);
    }
    
    //сохраняем файл пользователя
    if (isset($_FILES) && isset($_FILES["decode_file"]) && $_FILES["decode_file"]["error"] == UPLOAD_ERR_OK) {
        $name = 'files/decode/' . $_FILES["decode_file"]["name"];
        $extd = mb_strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
        if ($extd === 'bin') {
            move_uploaded_file($_FILES["decode_file"]["tmp_name"], $name);
            $bin = file_get_contents($name);
    
            $offset = 0;
            $alphabetLength = ord($bin[$offset++]);
            $alphabet = substr($bin, $offset, $alphabetLength);
            $offset += $alphabetLength;
    
            $leng = unpack('N', substr($bin, $offset, 4))[1];
            $offset += 4;
    
            $packedBits = substr($bin, $offset);
            $bitStream = $pack->unpackBinaryString($packedBits);
    
            $decoder = new AdaptiveArithmeticDecoder();
            $decoded = $decoder->decode($bitStream, $alphabet, $leng);
    
            $outPath = 'files/';
            $outName = uniqid('', true) . 'DECODE.txt';
            $full = $outPath . $outName;
            file_put_contents($outPath . $outName, $decoded);
        } else {
            $errors['decode_file'] = 'Ожидается бинарный файл .bin';
        }
    }
}

?>