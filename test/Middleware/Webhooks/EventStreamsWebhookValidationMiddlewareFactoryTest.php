<?php

declare(strict_types=1);

namespace Settermjd\MezzioTest\Twilio\Middleware\Webhooks;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Settermjd\Mezzio\Twilio\Exception\InvalidConfigException;
use Settermjd\Mezzio\Twilio\Middleware\Webhooks\EventStreamsWebhookValidationMiddleware;
use Settermjd\Mezzio\Twilio\Middleware\Webhooks\EventStreamsWebhookValidationMiddlewareFactory;

#[CoversClass(EventStreamsWebhookValidationMiddlewareFactory::class)]
class EventStreamsWebhookValidationMiddlewareFactoryTest extends TestCase
{
    public function testCanInstantiateMiddlewareIfConfigIsSetupProperly(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn([
                'twilio' => [
                    'auth_token' => '67890',
                ],
            ]);

        $factory = new EventStreamsWebhookValidationMiddlewareFactory();

        $this->assertInstanceOf(EventStreamsWebhookValidationMiddleware::class, $factory($container));
    }

    public function testThrowsExceptionIfConfigServiceIsNotAvailable(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Container does not have a config service');

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $factory = new EventStreamsWebhookValidationMiddlewareFactory();

        $this->assertInstanceOf(EventStreamsWebhookValidationMiddleware::class, $factory($container));
    }

    /**
     * @param array<string, string> $config
     */
    #[TestWith([[]], 'Test when the config is empty')]
    #[TestWith([
        [
            'twilio' => null,
        ],
    ], 'Test when the Twilio config is null')]
    #[TestWith([
        [
            'twilio' => [],
        ],
    ], 'Test when the Twilio config is empty')]
    #[TestWith([
        [
            'twilio' => [
                'account_sid' => null,
            ],
        ],
    ], 'Test when the Twilio config is missing the auth token')]
    #[TestWith([
        [
            'twilio' => [
                'auth_token' => '',
            ],
        ],
    ], 'Test with an empty auth token in the Twilio config')]
    public function testThrowsExceptionIfTwilioConfigIsNotSetupInConfigService(array|null $config): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Twilio configuration not set correctly');

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new EventStreamsWebhookValidationMiddlewareFactory();

        $this->assertInstanceOf(EventStreamsWebhookValidationMiddleware::class, $factory($container));
    }
}
