<?php
define('QUEUEFILE', '/var/curlforsure/requests.q');

// max retries before we give up
define('RETRY_MAX', 20);

// default # seconds between retries
define('RETRY_INTERVAL', 10);
?>
