CakePHP Exception Notifier
==========================

[![Bake Status](https://secure.travis-ci.org/lsenft/cakephp_exception_notifier.png?branch=master)](http://travis-ci.org/lsenft/cakephp_exception_notifier)

This is a CakePHP plugin. When an exception or a error occurs in your CakePHP application, this plugin sends you an email.
This component is inspired by exception_notification (Ruby on Rails plugin), see <a href="http://github.com/rails/exception_notification">http://github.com/rails/exception_notification</a>.

Installation
____________

Add repository and package to local composer.json.

```JSON
{
    "require" : {
        "lsenft/cakephp_exception_notifier": "*"
    },
    "repositories": [
        {
            "url": "https://github.com/lsenft/cakephp_exception_notifier.git",
            "type": "vcs"
        }
    ]
}
```

Update composer libraries.

```BASH
$ composer update
```

Add plugin to cake project


```
$ bin/cake plugin load lsenft/cakephp_exception_notifier
```

Usage
-----

First, put exception_notifier.php on app/controllers/components in your CakePHP application.
Second, put <a href="http://hal456.net/qdmail/">qdmail.php</a> on app/controllers/components too because excpetion_notifier.php depends on qdmail.php.
Then, add the following code in whichever controller you want to generate error emails (typically AppController). (Change "abc@example.com" to the recipient's mail address)

```
<?php
class AppController extends Controller
{
    public $components = array('ExceptionNotifier');

    public function beforeFilter()
    {
        $this->ExceptionNotifier->exceptionRecipients = array('abc@example.com');
        $this->ExceptionNotifier->observe();
    }
}
```

And that's all!

This component is only run when DEBUG configuraton value is 0. If you want to run this component when configuration value is more than 0, pass true to observe method.

```
$this->ExceptionNotifier->observe(true);
```


Exception error configuration
-----------------------------

In default configuration, this component observes exception, notice error, warning error, and fatal error.
If you don't want to observe notice error and warning error, add the following code.

```
class AppController extends Controller
{
    public $components = array('ExceptionNotifier');

    public function beforeFilter()
    {
        $this->ExceptionNotifier->exceptionRecipients = array('abc@example.com');
        $this->ExceptionNotifier->observeNotice = false;    // don't observe notice error
        $this->ExceptionNotifier->observeWarning = false;   // don't observe warning error
        $this->ExceptionNotifier->observe();
    }
}
```

If you want to observe strict error, add the following code.

```
class AppController extends Controller
{
    public $components = array('ExceptionNotifier');

    public function beforeFilter()
    {
        $this->ExceptionNotifier->exceptionRecipients = array('abc@example.com');
        $this->ExceptionNotifier->observeStrict = true;    // observe strict error
        $this->ExceptionNotifier->observe();
    }
}
```

Mail configuration
----------------------

If you use SMTP protocol when this component send exception mail, add the following code.

```
class AppController extends Controller
{
    public $components = array('ExceptionNotifier');

    public function beforeFilter()
    {
        $this->ExceptionNotifier->exceptionRecipients = array('abc@example.com');
        $this->ExceptionNotifier->useSmtp = true; // use SMTP
        $this->ExceptionNotifier->smtpParams = array(
                                  'host'=>'smtp.example.com',
                                  'port'=>'25',
                                  'from'=>'abc@example.com',
                                  'protocol'=>'SMTP',
                               );
        $this->ExceptionNotifier->observe();
    }
}
```


Copyright
---------

Copyright (c) 2009-2010 milk1000cc, released under the MIT license.
