# [![](https://bitbag.io/wp-content/uploads/2020/10/mollie-1024x535.png)](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie)

# Mollie Payments Plugin for Sylius
----

[![](https://img.shields.io/packagist/l/bitbag/mollie-plugin.svg) ](https://packagist.org/packages/bitbag/mollie-plugin "License") [ ![](https://img.shields.io/packagist/v/bitbag/mollie-plugin.svg) ](https://packagist.org/packages/bitbag/mollie-plugin "Version") [ ![](https://img.shields.io/scrutinizer/g/BitBagCommerce/SyliusMolliePlugin.svg) ](https://scrutinizer-ci.com/g/BitBagCommerce/SyliusMolliePlugin/ "Scrutinizer") [![](https://poser.pugx.org/bitbag/mollie-plugin/downloads)](https://packagist.org/packages/bitbag/mollie-plugin "Total Downloads") [![Slack](https://img.shields.io/badge/community%20chat-slack-FF1493.svg)](http://sylius-devs.slack.com) [![Support](https://img.shields.io/badge/support-contact%20author-blue])](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie)

At BitBag we do believe in open source. However, we are able to do it just because of our awesome clients, who are kind enough to share some parts of our work with the community. Therefore, if you feel like there is a possibility for us working together, feel free to reach us out. You will find out more about our professional services, technologies and contact details at [https://bitbag.io/](https://bitbag.io/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie).

## Table of Content

***

* [Overwiev](#overwiev)
* [Support](#we-are-here-to-help)
* [Installation](#installation)
  * [Requirements](#requirements)
  * [Usage](#usage)
  * [Customization](#customization)
  * [Testing](#testing)
  * [Recurring subscription](#recurring-subscription)
  * [Frontend part](#frontend-part)
* [About us](#about-us)
    * [Community](#community)
* [Demo Sylius shop](#demo-sylius-shop)
* [Additional Sylius resources for developers](#additional-resources-for-developers)
* [License](#license)
* [Contact](#contact)

# Overview
----

![Screenshot showing payment methods show in shop](doc/payment_methods_shop.png)

![Screenshot showing payment methods show in admin](doc/payment_methods_admin.png)

![Screenshot showing payment method config in admin](doc/payment_method_config.png)

Mollie is the most popular and advanced payment gateway integration with Sylius. This plugin is officially certified by Mollie. The integration currently supports the following payment methods:

1. Credit Cards (Master Card, VISA, American Express)
2. PayPal
3. ApplePay
4. Klarna
5. iDEAL
6. SEPA
7. SOFORT
8. EPS
9. Giropay
10. KBC/CBC Payment Button
11. Przelewy24
12. ING Home'Pay
13. Belfius Pay Button
14. Gift cards
15. Apple Pay Direct

Few words from Mollie: Our mission is to create a greater playing field for everyone. By offering convenient, safe world-wide payment solutions we remove barriers so you could focus on growing your business. Being authentic is our baseline.

Mollie is one of Europe's fastest-growing fin-tech companies. We provide a simple payment API, that enables webshop and app builders to implement more than twenty different payment methods in one go. Our packages and plugins are completely open-source, freely available, and easy to integrate into your current project.

Mollie thrives on innovation. When we started we spearheaded the payments industry by introducing effortless payment products that were easier, cheaper, and more flexible than what the rigid, cumbersome banks could do. Now, more than a decade later, trusted by 70.000+ businesses, Mollie is still building innovative products and working hard to make payments better.

## We are here to help
This **open-source plugin was developed to help the Sylius community** and make Mollie payments platform available to any Sylius store. If you have any additional questions, would like help with installing or configuring the plugin or need any assistance with your Sylius project - let us know!

[![](https://bitbag.io/wp-content/uploads/2020/10/button-contact.png)](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie)


# Installation
----

### Requirements

We work on stable, supported and up-to-date versions of packages. We recommend you to do the same.

| Package | Version |
| --- | --- |
| PHP |  ^7.2 |
| ext-json: * |  |
| mollie/mollie-api-php |  ^2.0 |
| sylius/admin-order-creation-plugin |  ^0.9.0 |
| sylius/refund-plugin |  ^1.0.0-RC.3 |
| sylius/sylius |  ^1.7.0 |

----


For the full installation guide please go to [installation](doc/installation.md)

## Usage
----
During configuration first, save the keys to the database and then click "Load methods"

### Rendering Mollie credit card form

You can use `BitBagSyliusMolliePlugin:DirectDebit:_form.html.twig` and `@BitBagSyliusMolliePlugin/Grid/Action/cancelSubscriptionMollie.html.twig` templates for adding the form to supplementing the direct debit card data from and cancel the subscription form the Twig UI.

For an example on how to do that, take a look at [these source files](tests/Application/templates/bundles/SyliusShopBundle).

## Customization
----
##### You can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) available services and [extend](https://symfony.com/doc/current/form/create_form_type_extension.html) current forms.

Run the below command to see what Symfony services are shared with this plugin:

```
$ bin/console debug:container bitbag_sylius_mollie_plugin
```

## Recurring subscription
----
### State Machine

For a better integration with Mollie's recurring subscription, [you can use state machine callback.](http://docs.sylius.com/en/1.1/customization/state_machine.html#how-to-add-a-new-callback)

Available states:

* Processing: Subscription created but not active yet (start date higher than "now")
* Active: Subscription is in progress. Not all payments are done, but we wait until the next payment date
* Cancelled: The merchant cancelled the subscription
* Suspended: Mandates became invalid, so the subscription is suspended
* Completed: All subscription payments are executed according to the timetable

## Frontend part
----
### Starting and building assets

* Go to `./tests/Application/` directory
* Run `gulp watch` in terminal. It will watch your changes in: 
  `../../src/Resources/public/js/Admin/**/*.js`, `../../src/Resources/public/css/**/*.css`

### Rebuilding assets

* `bin/console assets:install` or
* `gulp buildJsAssets` and
* `gulp buildCssAssets`

more details in `./tests/Application/gulpfile.babel.js`

### CSS & JS files directory

* CSS: go to `./src/Resources/public/css/**/`
* JS: go to `./src/Resources/public/js/**/`

## Testing
----
```
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run gulp
$ bin/console assets:install -e test
$ bin/console doctrine:database:create -e test
$ bin/console doctrine:schema:create -e test
$ bin/console server:run 127.0.0.1:8080 -e test
$ open http://localhost:8080
$ bin/behat
$ bin/phpspec run
```

# About us
---

BitBag is a company of people who **love what they do** and do it right. We fulfill the eCommerce technology stack with **Sylius**, Shopware, Akeneo and Pimcore for PIM, eZ Platform for CMS and VueStorefront for PWA. Our goal is to provide real digital transformation with an agile solution that scales with the **clients’ needs**. Our main area of expertise includes eCommerce consulting and development for B2C, B2B, and Multi-vendor Marketplaces.</br>
We are advisers in the first place. We start each project with a diagnosis of problems, and an analysis of the needs and **goals** that the client wants to achieve.</br>
We build **unforgettable**, consistent digital customer journeys on top of the **best technologies**.Based on a detailed analysis of the goals and needs of a given organization we create dedicated systems and applications that let businesses grow.<br>
Our team is fluent in **Polish, English, German and French**. That is why our cooperation with clients from all over the world is smooth.

**Some numbers from BitBag regarding Sylius:**
- 50+ **experts** including consultants, UI/UX designers, Sylius trained front-end and back-end developers,
- 120+ projects **delivered** on top of Sylius,
- 25+ **countries** of BitBag’s customers,
- 4+ **years** in the Sylius ecosystem.

**Our services:**
- Business audit/Consulting in the field of **strategy** development,
- Data/shop **migration**,
- Headless **eCommerce**,
- Personalized **software** development,
- **Project** maintenance and long term support,
- Technical **support**.

**Key clients:** Mollie, Guave, P24, Folkstar, i-LUNCH, Elvi Project, WestCoast Gifts.

---

If you need some help with Sylius development, don't be hesitated to contact us directly. You can fill the form on [this site](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie) or send us an e-mail to hello@bitbag.io!

---

[![](https://bitbag.io/wp-content/uploads/2020/10/badges-sylius.png)](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie)

## Community
----
For online communication, we invite you to chat with us & other users on [Sylius Slack](https://sylius-devs.slack.com/).

# Demo Sylius Shop

---

We created a demo app with some useful use-cases of plugins!
Visit [sylius-demo.bitbag.io](https://sylius-demo.bitbag.io/) to take a look at it. The admin can be accessed under
[sylius-demo.bitbag.io/admin/login](https://sylius-demo.bitbag.io/admin/login) link and `sylius: sylius` credentials.
Plugins that we have used in the demo:

| BitBag's Plugin | GitHub | Sylius' Store|
| ------ | ------ | ------|
| ACL Plugin | *Private. Available after the purchasing.*| https://plugins.sylius.com/plugin/access-control-layer-plugin/|
| Braintree Plugin | https://github.com/BitBagCommerce/SyliusBraintreePlugin |https://plugins.sylius.com/plugin/braintree-plugin/|
| CMS Plugin | https://github.com/BitBagCommerce/SyliusCmsPlugin | https://plugins.sylius.com/plugin/cmsplugin/|
| Elasticsearch Plugin | https://github.com/BitBagCommerce/SyliusElasticsearchPlugin | https://plugins.sylius.com/plugin/2004/|
| Mailchimp Plugin | https://github.com/BitBagCommerce/SyliusMailChimpPlugin | https://plugins.sylius.com/plugin/mailchimp/ |
| Multisafepay Plugin | https://github.com/BitBagCommerce/SyliusMultiSafepayPlugin |
| Wishlist Plugin | https://github.com/BitBagCommerce/SyliusWishlistPlugin | https://plugins.sylius.com/plugin/wishlist-plugin/|
| **Sylius' Plugin** | **GitHub** | **Sylius' Store** |
| Admin Order Creation Plugin | https://github.com/Sylius/AdminOrderCreationPlugin | https://plugins.sylius.com/plugin/admin-order-creation-plugin/ |
| Invoicing Plugin | https://github.com/Sylius/InvoicingPlugin | https://plugins.sylius.com/plugin/invoicing-plugin/ |
| Refund Plugin | https://github.com/Sylius/RefundPlugin | https://plugins.sylius.com/plugin/refund-plugin/ |

**If you need an overview of Sylius' capabilities, schedule a consultation with our expert.**

[![](https://bitbag.io/wp-content/uploads/2020/10/button_free_consulatation-1.png)](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_catalog)

## Additional resources for developers

---
To learn more about our contribution workflow and more, we encourage you to use the following resources:
* [Sylius Documentation](https://docs.sylius.com/en/latest/)
* [Sylius Contribution Guide](https://docs.sylius.com/en/latest/contributing/)
* [Sylius Online Course](https://sylius.com/online-course/)

## License
 ---

This plugin's source code is completely free and released under the terms of the MIT license.

[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen.)

## Contact
---
If you want to contact us, the best way is to fill the form on [our website](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie) or send us an e-mail to hello@bitbag.io with your question(s). We guarantee that we answer as soon as we can!

[![](https://bitbag.io/wp-content/uploads/2020/10/footer.png)](https://bitbag.io/contact-us/?utm_source=github&utm_medium=referral&utm_campaign=plugins_mollie)
