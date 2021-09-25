<?php
    $path = "data/data.csv";
    $f = file_get_contents($path);
    $lines = explode("\n", $f);
    echo implode("<br>", $lines);
?>