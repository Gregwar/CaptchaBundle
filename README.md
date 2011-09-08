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

**Using the vendors script **

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

Form theming
============

If you want to put the image in an other way, you can form theme `captcha_bundle` (this
is the default behavior) :

```html
{% block captcha_widget %}
    <img src="{{ captcha_code }}" title="captcha" width="120" height="40" />
    {{ form_widget(form) }}
{% endblock %}
```

License
=======
This bundle is under the MIT license. See the complete license in the bundle:
    LICENSE

