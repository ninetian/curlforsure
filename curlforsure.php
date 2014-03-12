<?php
require "config.php";
error_reporting(E_ALL ^ E_NOTICE);

class CurlForSure {
  private $handle;
  private $opts;
  private $retries_left;

  public function __construct($opts=array(), $retries=RETRY_MAX) {
    $this->handle = curl_init();
    if (!$this->handle)
      throw new Exception("Couldn't initalize cURL handle");

    $this->retries_left = $retries;
    $this->opts = array();
    $this->setopt_bulk($opts);
    $this->setopt(CURLOPT_HEADER, 1);
    $this->setopt(CURLOPT_RETURNTRANSFER, 1);
  }

  public function __destruct() {
    curl_close($this->handle);
  }

  public function setopt($name, $val) {
    curl_setopt($this->handle, intval($name), $val);
    $this->opts[$name] = $val;
  }

  public function setopt_bulk($opts) {
    foreach($opts as $name => $val) {
      $this->setopt($name, $val);
    }
  }

  public function exec() {
    $result = curl_exec($this->handle);
    if (empty($result)) {
      throw new Exception(curl_error($this->handle));
      curl_close($this->handle);
    }
    else {
      $info = curl_getinfo($this->handle);
      if (empty($info['http_code'])) {
        throw new Exception("No HTTP code returned");
        curl_close($this->handle);
      }
      else {
        $status_code = (int)$info['http_code'];
        switch($status_code) {
        case 429:
          if (preg_match("/Retry-After: (\d+)/im", $result, $matches)) {
            $secs_to_retry = intval($matches[1]);
            if ($this->retries_left > 0) {
              error_log("[$status_code]: retrying in $secs_to_retry");
              $this->queue($secs_to_retry);
            }
          }
          break;
        case 500:
        case 503:
        case 504:
        case 507:
        case 509:
        case 522:
          $secs_to_retry = RETRY_INTERVAL;
          if ($this->retries_left > 0) {
            error_log("[$status_code]: retrying in $secs_to_retry");
            $this->queue($secs_to_retry);
          }
          break;
        }

        return $status_code;
      }
    }
    return -1;
  }

  private function queue($secs_to_retry) {
    $fp = fopen(QUEUEFILE, "a");
    flock($fp, LOCK_EX);

    $record = array(
      "time_retry" => time() + $secs_to_retry,
      "opts" => $this->opts,
      "retries_left" => --$this->retries_left,
    );

    error_log("queued record");
    fwrite($fp, serialize($record)."\n");
    fflush($fp);

    flock($fp, LOCK_UN);
    fclose($fp);
  }
}
?>
