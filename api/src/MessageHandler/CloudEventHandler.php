<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Event;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use CloudEvents\V1\CloudEventImmutable;
use CloudEvents\V1\CloudEventInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class CloudEventHandler
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly KnativeCloudEventEmitter $eventEmitter,
        private readonly UriFactoryInterface $uriFactory,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CloudEventImmutable $event): void
    {
        /** @var Subscriber $subscriber */
        foreach ($this->subscriberRepository->findAll() as $subscriber) {
            if (!$subscriber->matches($event)) {
                continue;
            }

            $events = $this->eventEmitter->emitOne(
                $event,
                $this->uriFactory->createUri($subscriber->serviceUri),
                'POST',
                $subscriber->verifyPeer,
            );

            foreach ($events as $event) {
                if (!$event instanceof CloudEventInterface
                    || CloudEventInterface::SPEC_VERSION !== $event->getSpecVersion()
                ) {
                    $this->logger->warning('Invalid event received.', ['event' => $event]);
                    continue;
                }

                $entity = Event::fromCloudEvent($event);
                $this->entityManager->persist($entity);

                $this->messageBus->dispatch($event);
            }
            $this->entityManager->flush();
        }
    }
}
