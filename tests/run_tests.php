<?php
require_once dirname(__FILE__)."/../curlforsure.php";

for ($i=0; $i < 20; $i++) {
  $curl = new CurlForSure();

  $curl->setopt(CURLOPT_URL, "http://localhost:8000");
  $curl->setopt(CURLOPT_POST, 1);
  $curl->setopt(CURLOPT_RETURNTRANSFER, 1);
  $curl->setopt(CURLOPT_POSTFIELDS, http_build_query(array("foo" => "bar")));

  $resp = $curl->exec();
  echo $resp;
}
?>
