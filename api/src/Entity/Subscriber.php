<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\QueryParameter;
use CloudEvents\V1\CloudEventInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    mercure: true,
    paginationClientItemsPerPage: true,
    paginationViaCursor: [
        ['field' => 'id', 'direction' => 'DESC'],
    ],
    paginationItemsPerPage: 100,
    paginationMaximumItemsPerPage: 500,
)]
#[QueryParameter(key: ':property', filter: SearchFilter::class)]
#[QueryParameter(key: 'sort[:property]', filter: OrderFilter::class)]
#[ApiFilter(RangeFilter::class, properties: ["id"])]
#[ApiFilter(OrderFilter::class, properties: ["id" => "DESC"])]
#[ORM\Entity]
#[ORM\Index(columns: ['service_uri'])]
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

    /**
     * Bearer authentication to the CloudEvents sink
     */
    #[ORM\Column(nullable: true, options: ['default' => null])]
    public ?string $bearerAuthentication;

    public function __construct(
        string $serviceUri,
        array $filters = [],
        bool $verifyPeer = false,
        ?string $authentication = null,
    ) {
        $this->id = new Ulid();
        $this->serviceUri = $serviceUri;
        $this->filters = $filters;
        $this->verifyPeer = $verifyPeer;
        $this->bearerAuthentication = $authentication;
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
