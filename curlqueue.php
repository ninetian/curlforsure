<?php
require "curlforsure.php";

error_reporting(E_ALL ^ E_NOTICE);
define('SLEEP_INTERVAL', 5);

while(true) {
  $fp = fopen(QUEUEFILE, "c+");
  $open = flock($fp, LOCK_EX | LOCK_NB);
  if (!$open) {
    sleep(SLEEP_INTERVAL);
  }
  else {
    $out_buf = "";
    $curl_data = array();

    while (($line = fgets($fp)) !== false) {
      $line = trim($line);

      // skip blank lines
      if (empty($line)) 
        continue;

      $obj = unserialize(trim($line));

      if ($obj["time_retry"] > time()) {
        $out_buf .= $line;
      }
      else {
        array_push($curl_data, array("opts" => $obj["opts"], "retries_left" => $obj["retries_left"]));
      }
    }

    ftruncate($fp, 0);
    fwrite($fp, $out_buf);
    fflush($fp);
    
    flock($fp, LOCK_UN);
    fclose($fp);

    foreach($curl_data as $dat) {
      $curl = new CurlForSure($dat["opts"], $dat["retries_left"]);
      $status = $curl->exec();
      if ($status < 400) 
        error_log("[$status]: request satisfied");
    }
  }
  sleep(SLEEP_INTERVAL);
}
?>
