Testing
=======

Make sure /var/curlforsure (or whatever you have set in config.php) is writable.

First start the test server.  This will return random http responses for GET/PUT/POST reqs.
```
python tests/test_server.py 
```

Start the queue.
```
php curlqueue.php

```

Run run_tests.php.  This fires off some requests to be picked up by curlqueue.
```
php tests/run_test.php
```

Watch output of curlqueue.php and what is being written to /var/curlforsure/requests.q
