Gregwar's CaptchaBundle
=====================

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YUXRLWHQSWS6L)

The `GregwarCaptchaBundle` adds support for a captcha form type for the
Symfony form component.

It uses [gregwar/captcha](https://github.com/Gregwar/Captcha) as captcha generator, which is a separate standalone library that can be used for none-symfony projects.

Compatibility with Symfony
==========================

| CaptchaBundle   | Symfony   | PHP      |
|:---------------:|:---------:|:--------:|
| 2.1.*           | 4.* - 5.* | >= 7.1   |
| 2.0.*           | 2.8 - 3.* | >= 5.3.9 |
| 1.*             | 2.1 - 2.7 | >= 5.3.0 |


Installation
============

### Step 1: Download the GregwarCaptchaBundle

Use composer require to download and install the package. 
At the end of the installation, the bundle is automatically registered thanks to the Symfony recipe.

``` bash
    composer require gregwar/captcha-bundle
```

If you don't use flex, register it manually:
```php
<?php
// config/bundles.php
return [
    // ...
    Gregwar\CaptchaBundle\GregwarCaptchaBundle::class => ['all' => true]
];
```

Configuration
=============

If you need to customize the global bundle configuration, you can create a  `/config/packages/gregwar_captcha.yaml` file with your configuration:
``` yaml
gregwar_captcha:
  width: 160
  height: 50
```

Usage
=====

You can use the "captcha" type in your forms this way:

``` php
<?php
use Gregwar\CaptchaBundle\Type\CaptchaType;
// ...
$builder->add('captcha', CaptchaType::class); // That's all !
// ...
```

Note that the generated image will, by default, be embedded in the HTML document
to avoid dealing with route and subrequests.

Options
=======

You can define the following configuration options globally:

* **image_folder**: name of folder for captcha images relative to public web folder in case **as_file** is set to true (default="captcha")
* **web_path**: absolute path to public web folder (default='%kernel.project_dir%/public')
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
* **background_images**: Sets custom user defined images as the captcha background (1 image is selected randomly). It is recommended to turn off all the effects on the image (ignore_all_effects). The full paths to the images must be passed.
* **interpolation**: enable or disable the interpolation on the captcha
* **ignore_all_effects**: Recommended to use when setting background images, will disable all image effects.
* **session_key**, if you want to host multiple CAPTCHA on the same page, you might have different session keys to ensure proper storage of the clear phrase for those different forms

Example :

``` php
<?php
use Gregwar\CaptchaBundle\Type\CaptchaType;
// ...
$builder->add('captcha', CaptchaType::class, array(
    'width' => 200,
    'height' => 50,
    'length' => 6,
));
```

You can also set these options for your whole application using the `gregwar_captcha`
configuration entry in your `config.yml` file:
``` yaml 
gregwar_captcha:
    width: 200
    height: 50
    length: 6
```

Translation
===========

The messages are using the translator, you can either change the `invalid_message` option or translate it. Any contribution about the language is welcome !

As URL
============
To use a URL to generate a captcha image, you must add the bundle's routing configuration to your `config/routes.yaml` file:

``` yaml 
gregwar_captcha_routing:
    resource: "@GregwarCaptchaBundle/Resources/config/routing/routing.yml"
```

This will use the bundle's route of `/generate-captcha/{key}` to handle the generation. If this route conflicts with an application route, you can prefix the bundle's routes when you import:

``` yaml 
gregwar_captcha_routing:
    resource: "@GregwarCaptchaBundle/Resources/config/routing/routing.yml"
    prefix: /_gcb
```

Since the session key is transported in the URL, it's also added in another session array, under the `whitelist_key` key, for security reasons

Form Theming
============

The widget support the standard Symfony theming, see the [documentation](http://symfony.com/doc/current/book/forms.html#form-theming) for details on how to accomplish this.

The default rendering is:

``` twig
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

