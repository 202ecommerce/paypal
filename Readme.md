# PayPal Official Module for PrestaShop

[![Coding Standart](https://github.com/202-ecommerce/paypal/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/202-ecommerce/paypal/actions/workflows/php.yml)

## About

Let customer pay with one of the payment methods :

On PrestaShop 1.7 :
- PayPal Express Checkout
- PayPal Express Checkout Cards
- PayPal Plus Germany, Mexico and Brazil

On PrestaShop 1.6 :
- PayPal Express Checkout
- Braintree cards
- PayPal Plus Germany

#### Product page on PrestaShop Addons:

https://addons.prestashop.com/en/payment-card-wallet/1748-paypal-official.html

#### Support on PrestaShop Addons:

https://addons.prestashop.com/en/contact-us?id_product=1748

## Module version guide

| PrestaShop version | Module version     |  Repo                | Doc                |  PHP Version |
|---------|------------|---------------------|---------------------|-------------|
| 1.5.x - 1.6.x     | 3.x        |  [release/3.14.0][paypal-3.12] | |   5.3 or greater    |
| 1.7.x    | 5.x        |  [master][paypal-4] | [User Guide][6]|   5.4 or greater    |

## Requirements

1. PHP version (check Module version guide)
2. TLS 1.2 & cURL 1.0.1c

The Payment Card Industry (PCI) Council has mandated that early versions of
TLS be retired from service. All organizations that handle credit card information
are required to comply with this standard. As part of this obligation, PayPal has
upgraded its services to require TLS 1.2 for all HTTPS connections.
Connections to the sandbox environment use only TLS 1.2.

For more official, relevant information, see the 2017-2018 Merchant Security
Roadmap Microsite:
* [TLS 1.2 and HTTP/1.1 Upgrade Microsite][4]
* [SSL Certificate Upgrade Microsite][5]

Check the version of your URL’s underlying security library. If you use OpenSSL
libraries, please update them to at least version 1.0.1c.

## Installation

To install module on PrestaShop, download zip package form [product page on PrestaShop Addons][addons].

This module contain composer.json file. If you clone or download the module from github
repository, run the ```composer install``` from the root module folder.

See the [composer documentation][composer-doc] to learn more about the composer.json file.

## Compiling assets
**For development**

We use _Webpack_ to compile our javascript and scss files.
In order to compile those files, you must :
1. have _Node 10+_ installed locally
2. run `npm install` in the root folder to install dependencies
3. then run `npm run watch` to compile assets and watch for file changes

**For production**

Run `npm run build` to compile for production.
Files are minified, `console.log` and comments dropped.

## Contributing

PrestaShop modules are open-source extensions to the PrestaShop e-commerce solution. Everyone is welcome and even encouraged to contribute with their own improvements.

### Requirements

Contributors **must** follow the following rules:

* **Make your Pull Request on the "develop" branch**, NOT the "master" branch.
* Do not update the module's version number.
* Follow [the coding standards][1].

### Process in details

Contributors wishing to edit a module's files should follow the following process:

1. Create your GitHub account, if you do not have one already.
2. Fork the paypal project to your GitHub account.
3. Clone your fork to your local machine in the ```/modules``` directory of your PrestaShop installation.
4. Create a branch in your local clone of the module for your changes.
5. Change the files in your branch. Be sure to follow [the coding standards][1]!
6. Push your changed branch to your fork in your GitHub account.
7. Create a pull request for your changes **on the _'develop'_ branch** of the module's project. Be sure to follow [the commit message norm][2] in your pull request. If you need help to make a pull request, read the [Github help page about creating pull requests][3].
8. Wait for one of the core developers either to include your change in the codebase, or to comment on possible improvements you should make to your code.

That's it: you have contributed to this open-source project! Congratulations!

### Command line launched by github actions

Please launch these command line before submitting a Pull Request.

#### phpcs fixer

```bash
~$ vendor/bin/php-cs-fixer --fix
```
#### phpstan

You need a docker container to launch phpstan:

```
# create the prestashop container
~$ docker run -tid --rm -v ps-volume:/var/www/html --name temp-ps prestashop/prestashop

# launch phpstan
~$ docker run --rm --volumes-from temp-ps -v $PWD:/var/www/html/modules/paypal -e _PS_ROOT_DIR_=/var/www/html --workdir=/var/www/html/modules/paypal phpstan/phpstan:0.12 analyse --configuration=/var/www/html/modules/paypal/202/phpstan/phpstan.neon
```


### phpunit

You need a docker container to launch phpunit

```
docker run -tid --rm -v $PWD:/var/www/html/modules/paypal --name temp-paypal-unittest-ps 202ecommerce/prestashop:1.7.8.7
docker exec -t temp-paypal-unittest-ps sh /var/www/html/modules/paypal/202/docker/run_for_unittest.sh
```

[1]: https://devdocs.prestashop.com/1.7/development/coding-standards/
[2]: http://doc.prestashop.com/display/PS16/How+to+write+a+commit+message
[3]: https://help.github.com/articles/using-pull-requests
[4]: https://www.paypal-notice.com/en/TLS-1.2-and-HTTP1.1-Upgrade/
[5]: https://www.paypal-notice.com/en/SSL-Certificate-Upgrade-Microsite/
[6]: https://help.202-ecommerce.com/wp-content/uploads/2019/12/User-guide-PayPal-PrestaShop-module-51.pdf
[paypal-3.12]: https://github.com/202-ecommerce/paypal/tree/release/3.14.0
[paypal-4]: https://github.com/202-ecommerce/paypal/tree/master
[addons]: https://addons.prestashop.com/en/payment-card-wallet/1748-paypal-official.html
[composer-doc]: https://getcomposer.org/doc/04-schema.md
