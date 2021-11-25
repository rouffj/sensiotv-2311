<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testRegister(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'SensioTV');
        
        $client->clickLink('Register');
        $this->assertSelectorTextContains('h1', 'Create your account');

        // When Failure on form
        $client->submitForm('Create your SensioTV account', [
            'user[firstName]' => '',
        ]) ;
        
        $this->assertEquals(4, $client->getCrawler()->filter('.form-error-icon')->count());
        
        // When success
        $client->submitForm('Create your SensioTV account', [
            'user[firstName]' => 'Joseph',
            'user[lastName]' => 'ROUFF',
            'user[email]' => 'joseph2@joseph.io',
            'user[password][first]' => 'testtest',
            'user[password][second]' => 'testtest',
            'user[cgu]' => true,
        ]);

        $this->assertEquals(0, $client->getCrawler()->filter('.form-error-icon')->count());
        $userRepo = $client->getContainer()->get(UserRepository::class);
        $user = $userRepo->findOneByEmail('joseph2@joseph.io');
        $this->assertEquals('joseph2@joseph.io', $user->getEmail());
        //var_dump($client->getResponse()->getContent());die;
    }
}