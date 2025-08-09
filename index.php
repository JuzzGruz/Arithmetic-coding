<?php
require_once('arifmetic.php');
?>
<meta charset="utf-8">
<html>
    <body>
        Кодирование текста
        <br>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <input type="text" name="encode_text" id="encode" required>
            <button type="submit" class="btn btn-primary">
                Submit
            </button>
            <p><input type="checkbox" name="txt" value="1"> Сохранить в текстовый файл</p>
        </form>
        Декодирование файла
        <?php
        if (isset($errors['decode_file'])) {
            echo $errors['decode_file'];
        }
        ?>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <input type="file" name="decode_file" id="decode" required>
            <button type="submit" class="btn btn-primary">
                Submit
            </button>
            <p><input type="checkbox" name="txt" value="1"> Декодировать из текстового файла</p>
        </form>
        <?php
        if (isset($full)) {
            echo "<a href='$full' download target='_blank'>Скачать файл</a>";
        }
        ?>
    </body>
</html>