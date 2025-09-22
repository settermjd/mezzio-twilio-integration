<?php

declare(strict_types=1);

namespace Settermjd\MezzioTest\Twilio\Factory;

use Faker\Factory;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Settermjd\Mezzio\Twilio\Factory\TwilioRestClientFactory;

class TwilioRestClientFactoryTest extends TestCase
{
    private ContainerInterface&MockObject $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testCanInstantiateSendGridObjectWhenCorrectConfigurationIsAvailabl(): void
    {
        $faker      = Factory::create();
        $accountSid = $faker->word();
        $authToken  = $faker->word();

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn([
                'twilio' => [
                    'account_sid' => $accountSid,
                    'auth_token'  => $authToken,
                ],
            ]);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $factory          = new TwilioRestClientFactory();
        $twilioRestClient = $factory($this->container);

        $this->assertSame($accountSid, $twilioRestClient->getUsername());
        $this->assertSame($authToken, $twilioRestClient->getPassword());
    }

    /**
     * @dataProvider invalidConfigurationDataProvider
     */
    public function testCannotInstantiateSendGridObjectWithoutAValidConfiguration(?array $configuration = null): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn($configuration);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $factory = new TwilioRestClientFactory();
        $factory($this->container);
    }

    public static function invalidConfigurationDataProvider(): array
    {
        return [
            [
                [],
            ],
            [
                null,
            ],
            [
                [
                    'twilio' => [
                        'account_sid' => '',
                    ],
                ],
            ],
            [
                [
                    'twilio' => [
                        'auth_token' => '',
                    ],
                ],
            ],
            [
                [
                    'twilio' => [],
                ],
            ],
            [
                [
                    'twilio' => null,
                ],
            ],
            [
                [
                    'twilio' => [
                        'account_sid' => '',
                        '_auth_token' => '',
                    ],
                ],
            ],
            [
                [
                    'twilio' => [
                        '_account_sid' => '',
                        'auth_token'   => '',
                    ],
                ],
            ],
        ];
    }

    public function testCannotInstantiateSendGridObjectIfApplicationConfigurationIsMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $factory = new TwilioRestClientFactory();
        $factory($this->container);
    }
}
