<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio\Middleware\Webhooks;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Settermjd\Mezzio\Twilio\Exception\InvalidConfigException;

use function array_key_exists;
use function assert;
use function is_array;

class EventStreamsWebhookValidationMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidConfigException
     */
    public function __invoke(ContainerInterface $container): EventStreamsWebhookValidationMiddleware
    {
        if (! $container->has('config')) {
            throw new InvalidConfigException('Container does not have a config service');
        }

        $config = $container->get('config');
        assert(is_array($config));
        if (
            array_key_exists('twilio', $config)
            && is_array($config['twilio'])
            && array_key_exists('auth_token', $config['twilio'])
            && $config['twilio']['auth_token'] !== ''
        ) {
            return new EventStreamsWebhookValidationMiddleware($config['twilio']);
        }

        throw new InvalidConfigException('Twilio configuration not set correctly');
    }
}
