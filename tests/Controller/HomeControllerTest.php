<?php

namespace App\Tests\Controller;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{

    public function testIndex()
    {
        // Create a client and make a request to the show action
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(2, '.group.relative');
    }

    public function testShow()
    {
        // Create a mock for the Destination entity
        $destination = $this->createMock(Destination::class);

        // Create a mock for the DestinationRepository
        $destinationRepository = $this->createMock(DestinationRepository::class);
        $destinationRepository->method('find')->willReturn($destination);

        // Create a client and make a request to the show action
        $client = static::createClient();
        $client->request('GET', '/show/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'paysage');
    }
}
