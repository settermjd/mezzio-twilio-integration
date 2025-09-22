<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio\Middleware\Webhook;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Settermjd\Mezzio\Twilio\Exception\InvalidWebhookSignatureException;
use Twilio\Security\RequestValidator;

use function array_key_exists;

/**
 * This class is a PSR-15-compliant middleware that validates that a request came from Twilio
 *
 * The intention of the class is to provide a piece of middleware that can be dropped in and used
 * in any PSR-15-compliant application, without having to provide any custom configuration,
 * nor needing to have any knowledge of how Twilio's webhooks security works.
 *
 * @see https://www.twilio.com/docs/usage/webhooks/webhooks-security
 */
readonly final class WebhookValidationMiddleware implements MiddlewareInterface
{
    public const string HEADER_TWILIO_SIGNATURE = 'X-Twilio-Signature';

    private RequestValidator $validator;

    /**
     * @throws InvalidArgumentException
     * @param array<string, string> $config
     */
    public function __construct(array $config = [])
    {
        if (
            ! array_key_exists('auth_token', $config)
            || $config['auth_token'] === ''
        ) {
            throw new InvalidArgumentException('Twilio Auth Token is missing or empty.');
        }

        $this->validator = new RequestValidator($config['auth_token']);
    }

    /**
     * This function validates if the request came from Twilio
     *
     * It does this using a combination of the X-Twilio-Signature, request URI, and request body,
     * and the RequestValidator provided in Twilio's PHP Helper Library
     *
     * @inheritDoc
     * @throws InvalidWebhookSignatureException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            ! $request->hasHeader(self::HEADER_TWILIO_SIGNATURE) ||
            $request->getHeaderLine(self::HEADER_TWILIO_SIGNATURE) === ''
        ) {
            throw new InvalidArgumentException(
                'The request does not contain a Twilio signature header.',
            );
        }

        $webhookIsValid = $this->validator->validate(
            $request->getHeaderLine(self::HEADER_TWILIO_SIGNATURE),
            (string) $request->getUri(),
            (string) $request->getBody()
        );

        if (! $webhookIsValid) {
            throw new InvalidWebhookSignatureException(
                "The webhook's signature failed validation",
            );
        }

        return $handler->handle($request);
    }
}
