---
layout: post
title: "Working with mails in dev environment"
date: 2013-09-23 16:32
comments: true
categories: php
---

If your application sends emails, you probably don't want emails to be sent when you are developing on your machine.

If you use nice libraries like [SwiftMailer](http://swiftmailer.org/), it is easy to use a mock instead of sending real emails.
But if you don't, you can act on PHP's configuration: instead of installing and using a real SMTP server on your machine, you can fake one using a simple script.

<!--more-->

The fake server will be a shell script: create it as `/usr/local/bin/sendmail-fake`:

```sh
#!/bin/bash
{
date
echo $@
cat
} >> /var/log/sendmail-fake.log
```

Set up file permissions and check that it works:

```sh
$ sudo chmod +x /usr/local/bin/sendmail-fake
$ sudo chmod 777 /var/log/sendmail-fake.log
$ /usr/local/bin/sendmail-fake
```

Now configure PHP to use it in the `php.ini`:

```ini
sendmail_path = /usr/local/bin/sendmail-fake
```

(and restart Apache)

**That's it!**

You can also see the emails content in `/var/log/sendmail-fake.log`.
