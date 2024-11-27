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
    mercure: false,
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
#[ApiFilter(SearchFilter::class, properties: ['label' => 'partial'])]
#[ORM\Entity]
#[ORM\Index(columns: ['service_uri'])]
#[ORM\Index(columns: ['label'])]
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
     * The Trigger destination service CloudEvent URI
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $label;

    /**
     * The Trigger filters
     *
     * @var list<array{
     *     field: "type"|"subject"|"source",
     *     type: "exact"|"prefix"|"suffix",
     *     value: string,
     * }>
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
    #[Assert\Type(Types::BOOLEAN)]
    public bool $verifyPeer = true;

    /**
     * Bearer authentication to the CloudEvents sink
     */
    #[ORM\Column(length: 65535, nullable: true, options: ['default' => null])]
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

    private function extractValue(string $field, CloudEventInterface $event): string
    {
        return match ($field) {
            'type' => $event->getType(),
            'subject' => $event->getSubject(),
            'source' => $event->getSource(),
        };
    }

    public function matches(CloudEventInterface $event): bool
    {
        foreach ($this->filters as $filter) {
            $matches = match ($filter['type']) {
                'exact' => strcmp($filter['value'], $this->extractValue($filter['field'], $event)) === 0,
                'prefix' => strrpos($this->extractValue($filter['field'], $event), $filter['value']) === 0,
                'suffix' => strpos($this->extractValue($filter['field'], $event), $filter['value']) === strlen($this->extractValue($filter['field'], $event)) - strlen($filter['value']),
            };

            if (!$matches) {
                return false;
            }
        }

        return true;
    }
}
