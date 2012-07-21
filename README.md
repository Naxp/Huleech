Huleech
=======

PHP script to download videos from Hulu.

You need to have php5-mcrypt and rtmpdump 2.4 installed.

Here is a deb package link for ubuntu:

```https://launchpad.net/~mario-sitz/+archive/playground/+build/2882813/+files/rtmpdump_2.4~20110711.gitc28f1bab-1ubuntu0~ppa1~oneiric1_i386.deb```

or build from source here:

```http://rtmpdump.mplayerhq.hu/```

This needs to be installed on a server in the USA/Canada.
Note that anonymous proxies and amazon EC2 IPs are all blocked by hulu, also that hulu plus videos only have a 90 second preview unless you can provide a hulu plus username & password (not implemented yet).

Here are some example GET requests.

```u=http://www.hulu.com/watch/381638 // Print a CSV list of quality choices.```

```u=http://www.hulu.com/watch/381638&choice=2 // Start downloading the video quality at index 2.```

```u=http://www.hulu.com/watch/381638&choice=data // Print basic information about the stream.```

```u=http://www.hulu.com/watch/381638&choice=rawxml // Print complete XML about the servers and qualities.```

```u=http://www.hulu.com/watch/381638&choice=command // Print full rtmpdump command```