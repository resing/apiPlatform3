<?php

namespace App\Tests\Functional;

use App\Factory\UserFactory;
use Zenstruck\Browser\Json;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserRessourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post('/api/users', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'username' => 'draggin_in_the_morning',
                    'password' => 'password',
                ]
            ])
            ->use(function (Json $json) {
                $json->assertMissing('password');
                $json->assertMissing('id');
            })
            ->assertStatus(201)
            ->assertSuccessful()
        ;
    }

    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                    'id' => 47,
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json']
            ])
            //->dump()
            ->assertStatus(200);
    }

}