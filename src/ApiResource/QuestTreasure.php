<?php

namespace App\ApiResource;

class QuestTreasure
{
    public function __construct(
        public string $name,
        public string $value,
        public int $coolFactor
    )
    {
    }
}