<?php

declare(strict_types=1);

namespace Settermjd\Mezzio\Twilio\WebhookValidatorTest\Middleware;

use InvalidArgumentException;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Stream;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Settermjd\Mezzio\Twilio\WebhookValidator\Exception\InvalidWebhookSignatureException;
use Settermjd\Mezzio\Twilio\WebhookValidator\Middleware\WebhookValidationMiddleware;
use Twilio\Security\RequestValidator;

use function hash;
use function http_build_query;

#[CoversClass(WebhookValidationMiddleware::class)]
class WebhookValidationMiddlewareTest extends TestCase
{
    /** @var string[] */
    private array $config;

    private string $baseUri;
    private vfsStreamDirectory $directory;
    private RequestHandlerInterface&MockObject $handler;
    private RequestValidator $requestValidator;

    public function setUp(): void
    {
        $this->config           = [
            'TWILIO_AUTH_TOKEN' => '11111111111111111111111111111111',
        ];
        $this->baseUri          = 'https://example.org';
        $this->directory        = vfsStream::setup(
            structure: [
                'body.txt' => "",
            ]
        );
        $this->handler          = $this->createMock(RequestHandlerInterface::class);
        $this->requestValidator = new RequestValidator($this->config['TWILIO_AUTH_TOKEN']);
    }

    /**
     * @param array<string, string|scalar> $queryParams
     */
    private function getUri(array $queryParams): string
    {
        return "{$this->baseUri}?" . http_build_query($queryParams);
    }

    /**
     * @param array<string, string|scalar> $queryParams
     */
    private function getServerRequest(
        array $queryParams,
        string|null $twilioSignature = null
    ): ServerRequestInterface {
        $request = new ServerRequestFactory()
            ->createServerRequest('GET', $this->getUri($queryParams))
            ->withBody(new Stream($this->directory->getChild('body.txt')->url()))
            ->withQueryParams($queryParams);

        if ($twilioSignature !== null) {
            $request = $request->withHeader('X-Twilio-Signature', $twilioSignature);
        }

        return $request;
    }

    /**
     * @param array<string, string|scalar> $queryParams
     */
    private function getSignature(array $queryParams): string
    {
        return $this->requestValidator
            ->computeSignature($this->getUri($queryParams));
    }

    public function testMiddlewareThrowsExceptionWhenTwilioAuthTokenIsMissingOrEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Twilio Auth Token is missing or empty.');

        new WebhookValidationMiddleware([])
            ->process(
                $this->getServerRequest([]),
                $this->handler
            );
    }

    public function testMiddlewareThrowsExceptionWhenTwilioSignatureHeaderIsMissingOrEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The request does not contain a Twilio signature header.");

        new WebhookValidationMiddleware($this->config)
            ->process(
                $this->getServerRequest([]),
                $this->handler
            );
    }

    public function testMiddlewareThrowsExceptionWhenWebhookIsInvalid(): void
    {
        $this->expectException(InvalidWebhookSignatureException::class);
        $this->expectExceptionMessage("The webhook's signature failed validation");

        $this->handler
            ->expects($this->never())
            ->method('handle');

        $queryParams = [
            "lat" => '13.4134995',
            "lng" => 'i18.6986795',
        ];

        new WebhookValidationMiddleware($this->config)
            ->process(
                $this->getServerRequest($queryParams, $this->getSignature($queryParams)),
                $this->handler
            );
    }

    public function testMiddlewareCanValidateWebhooks(): void
    {
        $queryParams = [
            "lat"        => '13.4134995',
            "lng"        => 'i18.6986795',
            'bodySHA256' => hash('sha256', ''),
        ];

        $request = $this->getServerRequest($queryParams, $this->getSignature($queryParams));
        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn(new TextResponse('Well, hello there.'));
        $response = new WebhookValidationMiddleware($this->config)
            ->process($request, $this->handler);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertEquals('Well, hello there.', (string) $response->getBody());
    }
}
