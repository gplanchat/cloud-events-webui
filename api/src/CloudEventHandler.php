<?php

declare(strict_types=1);

namespace App;

use App\Entity\Event;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use CloudEvents\Exceptions\InvalidPayloadSyntaxException;
use CloudEvents\Exceptions\MissingAttributeException;
use CloudEvents\Exceptions\UnsupportedContentTypeException;
use CloudEvents\Exceptions\UnsupportedSpecVersionException;
use CloudEvents\Http\MarshallerInterface;
use CloudEvents\Http\UnmarshallerInterface;
use CloudEvents\V1\CloudEventImmutable;
use CloudEvents\V1\CloudEventInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class CloudEventHandler
{
    public function __construct(
        private readonly SubscriberRepository $subscriberRepository,
        private readonly ClientInterface $httpClient,
        private readonly ClientInterface $unsecuredHttpClient,
        private readonly MarshallerInterface $marshaller,
        private readonly UnmarshallerInterface $unmarshaller,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(CloudEventImmutable $event): void
    {
        /** @var Subscriber $subscriber */
        foreach ($this->subscriberRepository->findAll() as $subscriber) {
            if (!$subscriber->matches($event)) {
                continue;
            }

            $request = $this->marshaller->marshalBinaryRequest($event, 'POST', $subscriber->serviceUri);

            try {
                if ($subscriber->verifyPeer) {
                    $response = $this->httpClient->sendRequest($request);
                } else {
                    $response = $this->unsecuredHttpClient->sendRequest($request);
                }

                if (!$response->hasHeader('Content-Type')
                    || intval($response->getHeaderLine('Content-Length')) <= 0
                    || intval($response->getHeaderLine('content-length')) <= 0) {
                    continue;
                }
            } catch (NetworkExceptionInterface $exception) {
                $this->logger->emergency($exception->getMessage(), ['exception' => $exception]);
                continue;
            } catch (RequestExceptionInterface $exception) {
                $this->logger->error($exception->getMessage(), ['exception' => $exception]);
                continue;
            } catch (ClientExceptionInterface $exception) {
                $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
                continue;
            }

            if (!in_array($response->getStatusCode(), [200, 201, 202], true)) {
                $this->logger->error($response->getReasonPhrase());
                continue;
            }

            try {
                $events = $this->unmarshaller->unmarshal($response);
            } catch (InvalidPayloadSyntaxException $exception) {
                throw new UnrecoverableMessageHandlingException('Could not unmarshal Cloud Events request properly: the payload syntax is invalid.', previous: $exception);
            } catch (MissingAttributeException $exception) {
                throw new UnrecoverableMessageHandlingException('Could not unmarshal Cloud Events request properly: a Cloud Events attribute is missing.', previous: $exception);
            } catch (UnsupportedContentTypeException $exception) {
                throw new UnrecoverableMessageHandlingException('Could not unmarshal Cloud Events request properly: the content type is not supported.', previous: $exception);
            } catch (UnsupportedSpecVersionException $exception) {
                throw new UnrecoverableMessageHandlingException('Could not unmarshal Cloud Events request properly: the spec version is not supported.', previous: $exception);
            }

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
