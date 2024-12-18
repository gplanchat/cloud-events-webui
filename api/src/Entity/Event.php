<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use CloudEvents\V1\CloudEventInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Delete(),
    ],
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
#[ORM\Entity]
#[ORM\Index(columns: ['event_id'])]
#[ORM\Index(columns: ['time'])]
class Event
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
     * The CloudEvent identifier
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $eventId;

    /**
     * The CloudEvent Specification version
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $specVersion;

    /**
     * The CloudEvent type
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $type;

    /**
     * The CloudEvent data
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank]
    public array $data;

    /**
     * The CloudEvent source
     */
    #[ORM\Column(nullable: true)]
    public ?string $source;

    /**
     * The CloudEvent subject
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?\DateTimeInterface $time;

    /**
     * The CloudEvent data Content-Type
     */
    #[ORM\Column(nullable: true)]
    public ?string $dataContentType;

    /**
     * The CloudEvent data Json Schema
     */
    #[ORM\Column(nullable: true)]
    public ?string $dataSchema;

    /**
     * The CloudEvent subject
     */
    #[ORM\Column(nullable: true)]
    public ?string $subject;

    /**
     * The CloudEvent extensions
     */
    #[ORM\Column(type: Types::JSON)]
    public array $extensions;

    public function __construct(
        string  $eventId,
        string  $specVersion,
        string  $type,
        array   $data,
        string  $source,
        ?\DateTimeInterface $time = null,
        ?string $dataContentType = null,
        ?string $dataSchema = null,
        ?string $subject = null,
        array $extensions = [],
    ) {
        $this->id = new Ulid();
        $this->eventId = $eventId;
        $this->specVersion = $specVersion;
        $this->type = $type;
        $this->data = $data;
        $this->source = $source;
        $this->time = $time;
        $this->dataContentType = $dataContentType;
        $this->dataSchema = $dataSchema;
        $this->subject = $subject;
        $this->extensions = $extensions;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public static function fromCloudEvent(CloudEventInterface $event): self
    {
        return new self(
            $event->getId(),
            $event->getSpecVersion(),
            $event->getType(),
            $event->getData(),
            $event->getSource(),
            $event->getTime(),
            $event->getDataContentType(),
            $event->getDataSchema(),
            $event->getSubject(),
            $event->getExtensions(),
        );
    }
}
