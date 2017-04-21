Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require db4y/cas-bundle "^1"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new db4y\CasBundle\db4yCasBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
----------------------------

The default configuration for the bundle looks like this:

```yaml
# app/config/config.yml

db4y_cas:
    cas:
        host: cas.unistra.fr
        port: 443
        context: /cas
    restricted: db4y_cas.restricted
```

Configure the security firewall:

```yaml
# app/config/security.yml

security:
    firewalls:
        main:
            anonymous: ~
            security: true
            pattern: ^/admin
            provider: your_provider
            guard:
                authenticators:
                    - db4y_cas.cas_authenticator
```

Add the routes to your app :

```yaml
# app/config/routing.yml

_db4y_cas:
    type: annotation
    resource: "@db4yCasBundle/Controller/"
    prefix: /
```

### Restricted access page

To customize this page, two options are available :

#### configure your own route 

Create a controller action and set the `restricted` config option 
to your route name.

#### Override the template

Override the template in app/Resources/db4yCasBundle/views/restricted.html.twig


