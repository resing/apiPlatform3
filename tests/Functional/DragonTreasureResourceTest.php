<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5, [
            'isPublished' => true
        ]);

        DragonTreasureFactory::createOne([
            'isPublished' => false
        ]);
        $json = $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
            ->json()
        ;

        $json->assertMatches('keys("hydra:member"[0])', [
            '@id',
            '@type',
            'name',
            'description',
            'value',
            'coolFactor',
            'owner',
            'shortDescription',
            'plunderedAtAgo',
        ]);
    }

    public function testPostToCreateTreasureWithApiKey(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE],
        ]);
        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(422)
        ;
    }

    public function testPostToCreateTreasureDeniedWithoutScope(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);
        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(403)
        ;
    }

    public function testPatchToUpdateTreasure(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);

        $user = $token->getOwnedBy();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user,
            'isPublished' => true
        ]);
        $this->browser()
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 1234,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 1234)
        ;
        $token2 = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);

        $this->browser()
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 6789,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$token2->getToken()
                ]
            ])
            ->assertStatus(403);

        $this->browser()
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'owner' => '/api/users/'.$token2->getOwnedBy()->getId()
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(403)
        ;
    }

    public function testAdminCanPatchToEditTreasure(): void
    {
        $token = ApiTokenFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true
        ]);

        $this->browser()
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
        ;
    }

    public function testGetOneUnpublishedTreasure404s(): void
    {

         $dragonTreasure = DragonTreasureFactory::createOne([
            'isPublished' => false
        ]);
        $token = ApiTokenFactory::new()->asAdmin()->create();
        $this->browser()
            ->get('/api/treasures/'.$dragonTreasure->getId(), [
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(404)
        ;

    }

    public function testPatchUnpublishedWorks()
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);

        $user = $token->getOwnedBy();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user,
            'isPublished' => false,
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/treasures/'.$treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
        ;
    }

    public function testOwnerCanSeeIsPublishedField(): void
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);
        $user = $token->getOwnedBy();

        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->patch('/api/treasures/'. $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$token->getToken()
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', false)
        ;
    }
}