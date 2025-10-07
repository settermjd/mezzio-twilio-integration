<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio;

use Settermjd\Mezzio\Twilio\Factory\TwilioRestClientFactory;
use Settermjd\Mezzio\Twilio\Middleware\Webhooks\EventStreamsWebhookValidationMiddleware;
use Settermjd\Mezzio\Twilio\Middleware\Webhooks\EventStreamsWebhookValidationMiddlewareFactory;
use Settermjd\Validator\VerifyPhoneNumber;
use Twilio\Rest\Client;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array<string,array<string,array<class-string,class-string>>>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return array<string,array<class-string,class-string>>
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                EventStreamsWebhookValidationMiddleware::class => EventStreamsWebhookValidationMiddlewareFactory::class,
            ],
        ];
    }
}
