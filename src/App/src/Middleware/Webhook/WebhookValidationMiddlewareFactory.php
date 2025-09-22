<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio\Middleware\Webhook;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Settermjd\Mezzio\Twilio\Exception\InvalidConfigException;

class WebhookValidationMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidConfigException
     */
    public function __invoke(ContainerInterface $container): WebhookValidationMiddleware
    {
        if (! $container->has('config')) {
            throw new InvalidConfigException('Container does not have a config service');
        }

        $config = $container->get('config');
        if (
            array_key_exists('twilio', $config)
            && is_array($config['twilio'])
            && array_key_exists('auth_token', $config['twilio'])
            && $config['twilio']['auth_token'] !== ''
        ) {
            return new WebhookValidationMiddleware($config['twilio']);
        }

        throw new InvalidConfigException('Twilio configuration not set correctly');
    }
}
