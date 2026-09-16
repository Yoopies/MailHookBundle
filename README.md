MailHookBundle
=============================================================================
Catch webhook from various API mail service and directly get the related user.


[![Build Status](https://scrutinizer-ci.com/g/ScullWM/MailHookBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ScullWM/MailHookBundle/)
[![Build Status](https://scrutinizer-ci.com/g/ScullWM/MailHookBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ScullWM/MailHookBundle/build-status/master)


Features
--------
Define your project url to your mail provider service (Mandrill, MailJet...) and get events dispatched into your app.


Support various service
--------
- Campain Monitor
- Mailgun
- Mailjet
- Mandrill
- Sendgrid
- Sparkpost
- And add your custom service

Example use cases:
-------------
- Disable to a user's notification when a mail is signal as spam.
- Send a private message to a user if last mail send get bounced.
- Warning an Account Manager about a hard bounce on a new user creation.
- Track email reading and clicks on your custom CRM.
- All you want!


Installation
-----------------------------------

Add the package to your composer.json file
```
"scullwm/mailhookbundle": "dev-master",
```

Requires PHP 8.2+ and Symfony 6.4 or 7.x.

Add this to config/bundles.php
```php
<?php

return [
    // ...
    Swm\Bundle\MailHookBundle\SwmMailHookBundle::class => ['all' => true],
];
```

Configuration
-------------

### 1) Create config/packages/swm_mail_hook.yaml

```yaml
swm_mail_hook:
    secretsalt: notSecret
```

### 2) Create config/routes/swm_mail_hook.yaml

```yaml
swm_mailhook_controller:
    resource: "@SwmMailHookBundle/Controller/"
    type:     attribute
    prefix:   /
```

Well done!


Use it
------

Go to your email service provider like mandrillapp.com and find where to configure your webhook.
Check hooks you want and set the url like this:

http://www.mywebsite.com/webhook/{secretSalt}/{serviceName}/catch

With no configuration for mandrill it should be :
http://www.mywebsite.com/webhook/notSecret/mandrill/catch

With a custom secretSalt and mailjet service it should be :
http://www.mywebsite.com/webhook/dDifXo26/mailjet/catch


Events dispatched
-----------------

- swm.mail_hook.event.open
- swm.mail_hook.event.send
- swm.mail_hook.event.click
- swm.mail_hook.event.soft_bounce
- swm.mail_hook.event.hard_bounce
- swm.mail_hook.event.deferral
- swm.mail_hook.event.spam
- swm.mail_hook.event.blocked
- swm.mail_hook.event.unsub
- swm.mail_hook.event.reject
- swm.mail_hook.event.other


Events listener provided
------------------------

By default, a simple MailHookEvent is dispatched by the DefaultHydrator.
