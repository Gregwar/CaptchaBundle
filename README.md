Gregwar's CaptchaBundle
=====================

The `GregwarCaptchaBundle` adds support for a "captcha" form type for the
Symfony2 form component.

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
        git=git://github.com/Gregwar/CaptchaBundle.git
        target=/bundles/Gregwar/CaptchaBundle
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

### Step 2: Configure the Autoloader

Now you will need to add the `Gregwar` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamspaces(array(
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

Note that the generated image will be embeded in the HTML document, to avoid dealing
with route and subrequests.

Options
=======

You can define the following type option :

* **width**: the width of the captcha image (default=120)
* **height**: the height of the captcha image (default=40)
* **length**: the length of the captcha (number of chars, default 5)
* **quality**: jpeg quality of captchas (default=15)
* **charset**: the charset used for code generation (default=abcdefhjkmnprstuvwxyz23456789)
* **font**: the font to use (default=Generator/Font/captcha.ttf)
* **keep_value**: the value will be the same until the form is posted, even if the page is refreshed (default=true)
* **as_file**: if set to true an image file will be created instead of embedding to please IE6/7 (default=false)
* **image_folder**: name of folder for captcha images relative to public web folder in case **as_file** ist set to true (default="captcha")
* **web_path**: absolute path to public web folder (default="%kernel.root_dir%/../web")
* **gc_freq**: frequency of garbage collection in fractions of 1 (default=100)
* **expiration**: maximum lifetime of captcha image files in minutes (default=60)

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

Form theming
============

The widget support the standard symfony theming, see the [documentation](http://symfony.com/doc/current/book/forms.html#form-theming) for details on how to accomplish this.

The default rendering is:

```html
{% block captcha_widget %}
{% spaceless %}
    <img src="{{ captcha_code }}" title="captcha" width="{{ captcha_width }}" height="{{ captcha_height }}" />
    {{ form_widget(form) }}
{% spaceless %}
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

