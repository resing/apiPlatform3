<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DailyQuestResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testPatchCanUpdateStatus()
    {
        DragonTreasureFactory::createMany(5);
        $yesterday = new \DateTime('-2 day');
        $this->browser()
            ->patch('/api/quests/'.$yesterday->format('Y-m-d'), [
                'json' => [
                    'status' => 'completed',
                ],
                'headers' => [
                    'Content-type' => 'application/merge-patch+json'
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('status', 'completed');

    }
}