<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use CloudEvents\V1\CloudEventInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    mercure: true,
    paginationItemsPerPage: 100,
)]
#[ORM\Entity]
class Subscriber
{
    /**
     * The entity ID
     */
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private Ulid $id;

    /**
     * The Trigger filters
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank]
    public array $filters;

    /**
     * The Trigger destination service CloudEvent URI
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $serviceUri;

    /**
     * Wether we should check Trigger destination service CloudEvent URI's TLS certificate
     */
    #[ORM\Column(options: ['default' => true])]
    public bool $verifyPeer;

    public function __construct(
        string $serviceUri,
        array $filters = [],
        bool $verifyPeer = false,
    ) {
        $this->id = new Ulid();
        $this->serviceUri = $serviceUri;
        $this->filters = $filters;
        $this->verifyPeer = $verifyPeer;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function matches(CloudEventInterface $event): bool
    {
        foreach ($this->filters as $filter) {
            $matches = match ($filter['type']) {
                'exact' => strcmp($filter['value'], $event->getType()) === 0,
                'prefix' => strrpos($event->getType(), $filter['value']) === 0,
                'suffix' => strpos($event->getType(), $filter['value']) === strlen($event->getType()) - strlen($filter['value']),
            };

            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
