curlforsure
===========

Automated retry of php curl requests using a persistent queue system

### What is this for?

Suppose an API is giving you a 429 or a 503 with a Retry-After (or not).  If your server crashes or the link dies before you retry again, the day is lost.  The solution is to put the request in a persistent queue so it can be retried in a reliable, failsafe manner.

### Operating instructions

Make sure path to QUEUEFILE (defined in [config.php](config.php)) exists and is writable.

Run curlqueue.php
```
php curlqueue.php
```

Make requests like this:
```
require "curlforsure.php";

$curl = new CurlForSure();

$curl->setopt(CURLOPT_URL, "http://localhost:8000");
$curl->setopt(CURLOPT_POST, 1);
$curl->setopt(CURLOPT_RETURNTRANSFER, 1);
$curl->setopt(CURLOPT_POSTFIELDS, http_build_query(array("foo" => "bar")));

$curl->exec();

```
Now the request is sent to the queue to be retried according to settings in config.php
