Gregwar's CaptchaBundle
=====================

`GregwarCaptchaBundle` provides the form type "captcha"

Installation
============

To install `GregwarCaptchaBundle`, first adds it to your `deps`:

    [GregwarCaptchaBundle]
        git=git://github.com/Gregwar/CaptchaBundle.git
        target=/bundles/Gregwar/CaptchaBundle

And run `php bin/vendors install`. Then add the namespace to your `app/autoload.php` 
file:

```php
<?php
...
'Gregwar' => __DIR__.'/../vendor/bundles',
...
```

And registers the bundle in your `app/AppKernel.php`:

```php
<?php
//...
public function registerBundles()
{
    $bundles = array(
        ...
        new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
        ...
    );
...
```

Adds the following configuration to your `app/config/config.yml`:

    gregwar_captcha: ~

Usage
=====

You can use the "captcha" type in your forms this way:

```php
<?php
    // ...
    $builder->add('captcha', 'captcha'); // That's all !
    // ...
```

Note that the generated image will be embeded in the HTML document, to avoid dealing
with route and subrequests.

License
=======

This bundle is under MIT license
