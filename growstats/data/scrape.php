<?php
    function scrape($date_now) {
        try {
            $path = "data.csv";
            $res = file_get_contents("https://growtopiagame.com/detail");
            $data = json_decode($res);
            $online = $data->{'online_user'};

            $linecount = 0;
            $handle = fopen($path, "r");
            while(!feof($handle)){
              $line = fgets($handle);
              $linecount++;
            }
            fclose($handle);
            
            $fp = fopen($path, 'a');
            # if line count is more than 2520 (last 42 hours worth of lines)
            if ($linecount >= 2520) {
                removeLines($path, 0);
                if ($online != "") {
                    fwrite($fp, "\n".$date_now.",".$online);
                } else { }
            } else {
                if (filesize($path) == 0) {
                    if ($online != "") {
                        fwrite($fp, $date_now.",".$online);
                    } else { }
                } else {
                    if ($online != "") {
                        fwrite($fp, "\n".$date_now.",".$online);
                    } else { }
                }
            }
            fclose($fp);
        } catch (Exception $e) {
            echo $e;
            die();
        }
    }
    function removeLines($path, $line) {
        $file = file($path);
        $output = $file[$line];
        unset($file[$line]);
        file_put_contents($path, $file);
    }

    // Run the program
    for($i = 0; $i < 5; $i++) {
        $date_now = date("Y-m-d H:i:s");
        scrape($date_now);
        sleep(60);
    }
?>
