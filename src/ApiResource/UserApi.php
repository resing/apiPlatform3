<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;

#[ApiResource(
    shortName: 'User',
    paginationItemsPerPage: 5,
    provider: EntityToDtoStateProvider::class,
    processor: EntityClassDtoStateProcessor::class,
    stateOptions: new Options(entityClass: User::class),
)]
class UserApi
{
    #[ApiProperty(readable: false,writable: false, identifier: true)]
    public ?int $id = null;

    public ?string $email=  null;

    public ?string $username = null;

    /**
     * the plaintext password
     */
    #[ApiProperty(readable: false)]
    public ?string $password = null;

    /**
     * @var array<int, DragonTreasure>
     */
    #[ApiProperty(writable: false)]
    public array $dragonTreasures= [];

    #[ApiProperty(writable: false)]
    public int $flameThrowingDistance = 0;
}