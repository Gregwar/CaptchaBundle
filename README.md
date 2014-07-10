Gregwar's CaptchaBundle
=====================

The `GregwarCaptchaBundle` adds support for a "captcha" form type for the
Symfony2 form component.

Important note: the master of this repository is containing current development
in order to work with Symfony 2.1. If you are using Symfony 2.0 please checkout
the 2.0 branch.

Installation
============

### Step 1: Download the GregwarCaptchaBundle

Ultimately, the GregwarCaptchaBundle files should be downloaded to the
'vendor/bundles/Gregwar/CaptchaBundle' directory.

You can accomplish this several ways, depending on your personal preference.
The first method is the standard Symfony2 method.

***Using the vendors script***

Add the following lines to your `deps` file:

```
    [GregwarCaptchaBundle]
        git=http://github.com/Gregwar/CaptchaBundle.git
        target=/bundles/Gregwar/CaptchaBundle
        version=origin/2.0 <- add this if you are using Symfony 2.0
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

***Using submodules***

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/Gregwar/CaptchaBundle.git vendor/bundles/Gregwar/CaptchaBundle
$ git submodule update --init
```

***Using Composer***

Add the following to the "require" section of your `composer.json` file:

```
    "gregwar/captcha-bundle": "dev-master"
```

And update your dependencies

### Step 2: Configure the Autoloader

If you use composer, you can skip this step.

Now you will need to add the `Gregwar` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Gregwar' => __DIR__.'/../vendor/bundles',
));
```
### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

```php
<?php
// app/appKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
    );
}
```

Configuration
=============

Add the following configuration to your `app/config/config.yml`:

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

Note that the generated image will, by default, be embedded in the HTML document
to avoid dealing with route and subrequests.

Options
=======

You can define the following configuration options globally:

* **image_folder**: name of folder for captcha images relative to public web folder in case **as_file** is set to true (default="captcha")
* **web_path**: absolute path to public web folder (default="%kernel.root_dir%/../web")
* **gc_freq**: frequency of garbage collection in fractions of 1 (default=100)
* **expiration**: maximum lifetime of captcha image files in minutes (default=60)

You can define the following configuration options globally or on the CaptchaType itself:

* **width**: the width of the captcha image (default=120)
* **height**: the height of the captcha image (default=40)
* **disabled**: disable globally the CAPTCHAs (can be useful in dev environment), it will
  still appear but won't be editable and won't be checked
* **length**: the length of the captcha (number of chars, default 5)
* **quality**: jpeg quality of captchas (default=30)
* **charset**: the charset used for code generation (default=abcdefhjkmnprstuvwxyz23456789)
* **font**: the font to use (default is random among some pre-provided fonts), this should be an absolute path
* **keep_value**: the value will be the same until the form is posted, even if the page is refreshed (default=true)
* **as_file**: if set to true an image file will be created instead of embedding to please IE6/7 (default=false)
* **as_url**: if set to true, a URL will be used in the image tag and will handle captcha generation. This can be used in a multiple-server environment and support IE6/7 (default=false)
* **invalid_message**: error message displayed when an non-matching code is submitted (default="Bad code value", see the translation section for more information)
* **bypass_code**: code that will always validate the captcha (default=null)
* **whitelist_key**: the session key to use for keep the session keys that can be used for captcha storage, when using as_url (default=captcha_whitelist_key)
* **reload**: adds a link to reload the code
* **humanity**: number of extra forms that the user can submit after a correct validation, if set to a value different of 0, only 1 over (1+humanity) forms will contain a CAPTCHA (default=0, i.e each form will contain the CAPTCHA)
* **distortion**: enable or disable the distortion on the image (default=true, enabled)
* **max_front_lines**, **max_behind_lines**: the maximum number of lines to draw on top/behind the image. `0` will draw no lines; `null` will use the default algorithm (the
number of lines depends on the size of the image). (default=null)
* **background_color**: sets the background color, if you want to force it, this should be an array of r,g &b, for instance [255, 255, 255] will force the background to be white
* **interpolation**: enable or disable the interpolation on the captcha

Example :

```php
<?php
    // ...
    $builder->add('captcha', 'captcha', array(
        'width' => 200,
        'height' => 50,
        'length' => 6,
    ));
```

You can also set these options for your whole application using the `gregwar_captcha`
configuration entry in your `config.yml` file:

    gregwar_captcha:
        width: 200
        height: 50
        length: 6

Translation
===========

The messages are using the translator, you can either change the `invalid_message` option or translate it. Any contribution about the language is welcome !

As URL
============
To use a URL to generate a captcha image, you must add the bundle's routing configuration to your app/routing.yml file:

    gregwar_captcha_routing:
        resource: "@GregwarCaptchaBundle/Resources/config/routing/routing.yml"

This will use the bundle's route of "/generate-captcha/{key}" to handle the generation. If this route conflicts with an application route, you can prefix the bundle's routes when you import:

    gregwar_captcha_routing:
        resource: "@GregwarCaptchaBundle/Resources/config/routing/routing.yml"
        prefix: /_gcb

Since the session key is transported in the URL, it's also added in another session array, under the `whitelist_key` key, for security reasons

Form Theming
============

The widget support the standard Symfony theming, see the [documentation](http://symfony.com/doc/current/book/forms.html#form-theming) for details on how to accomplish this.

The default rendering is:

```html
{% block captcha_widget %}
{% spaceless %}
    <img src="{{ captcha_code }}" title="captcha" width="{{ captcha_width }}" height="{{ captcha_height }}" />
    {{ form_widget(form) }}
{% endspaceless %}
{% endblock %}
```

Image creation
==============
If you choose to use image files instead of embedding the widget will execute a garbage collection
randomly and delete images that exceed the configured lifetime.

License
=======
This bundle is under the MIT license. See the complete license in the bundle:
    LICENSE

