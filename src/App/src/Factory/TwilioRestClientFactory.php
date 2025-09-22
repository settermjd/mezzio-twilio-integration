<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio\Factory;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Twilio\Rest\Client;

use function array_key_exists;
use function is_array;

/**
 * This class simplifies instantiating a Twilio Rest Client object from the application's configuration provided
 *
 * The __invoke method expects the application's conrfiguration to have at least the following structure:
 *
 * [
 *     'twilio' => [
 *         '_account_sid' => '',
 *         'auth_token' => '',
 *     ]
 * ]
 */
class TwilioRestClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        if (! $container->has('config')) {
            throw new InvalidArgumentException('Application configuration is missing.');
        }

        $configuration = $container->get('config');

        if (
            ! is_array($configuration)
            || ! array_key_exists('twilio', $configuration)
        ) {
            throw new InvalidArgumentException('Twilio configuration is missing.');
        }

        if (
            ! is_array($configuration['twilio'])
            || empty($configuration['twilio']['account_sid'])
            || empty($configuration['twilio']['auth_token'])
        ) {
            throw new InvalidArgumentException(
                'Twilio configuration is missing either the account SID or the auth token.'
            );
        }

        [
            'account_sid' => $accountSid,
            'auth_token'  => $authToken,
        ] = $configuration['twilio'];

        return new Client((string) $accountSid, (string) $authToken);
    }
}
