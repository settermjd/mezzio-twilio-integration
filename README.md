# Mezzio Twilio Integration
![PHP QA Workflow](https://github.com/settermjd/mezzio-twilio-webhook-validator/actions/workflows/php-qa.yml/badge.svg)

This is a small package that simplifies integrating Twilio into Mezzio projects.

## Installation

Install this package using Composer:

```bash
composer require settermjd/mezzio-twilio-integration
```

## Configuration

If you're using the package with Mezzio, copy the default configuration file, _config/autoload/twilio.global.php_, to _config/autoload_ in your project.
Then, either ensure that the `TWILIO_ACCOUNT_SID` and `TWILIO_AUTH_TOKEN` environment variables are set, or change the value of `account_sid` and `auth_token` in _config/autoload/twilio.global.php_ as appropriate.
I recommend using [PHP Dotenv](https://github.com/vlucas/phpdotenv) to set environment variables during development and your deployment tool otherwise.
>>>>>>> webhook/main
