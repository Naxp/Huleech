Huleech
=======

PHP script to download videos from Hulu.

This needs to be installed on a server in the USA.
Note that anonymous proxies and amazon EC2 IPs are all blocked by hulu.

Here are some example GET requests.

```u=http://www.hulu.com/watch/381638 // Print a CSV list of quality choices.```

```u=http://www.hulu.com/watch/381638&choice=2 // Start downloading the video quality at index 2.```

```u=http://www.hulu.com/watch/381638&choice=data // Print basic information about the stream.```

```u=http://www.hulu.com/watch/381638&choice=rawxml // Print complete XML about the servers and qualities.```

```u=http://www.hulu.com/watch/381638&choice=command // Print full rtmpdump command```