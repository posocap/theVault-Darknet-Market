<?php

declare(strict_types=1);

namespace VonageTest\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Account\ClientFactory;

class ClientFactoryTest extends TestCase
{
    public function testInvokeCreatesClientWithConfiguredApiResource(): void
    {
        $mockServices = [
            'account' => ClientFactory::class,
            APIResource::class => APIResource::class,
        ];

        $mockClient = $this->createMock(Client::class);
        $container = new MapFactory($mockServices, $mockClient);
        $factory = new ClientFactory();

        $result = $factory($container);
        $this->assertInstanceOf(\Vonage\Account\Client::class, $result);
        $this->assertEquals('/account', $result->getAPIResource()->getBaseUri());
        $this->assertInstanceOf(Client\Credentials\Handler\BasicHandler::class, $result->getAPIResource()
            ->getAuthHandlers()[0]);
        $this->assertFalse($result->getAPIResource()->isHAL());
    }
}
