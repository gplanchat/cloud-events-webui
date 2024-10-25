<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use CloudEvents\Exceptions\InvalidPayloadSyntaxException;
use CloudEvents\Exceptions\MissingAttributeException;
use CloudEvents\Exceptions\UnsupportedContentTypeException;
use CloudEvents\Exceptions\UnsupportedSpecVersionException;
use CloudEvents\Http\UnmarshallerInterface;
use CloudEvents\V1\CloudEventInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/cloud-events', name: 'cloud_events', methods: ['POST'])]
final readonly class CloudEventController
{
    public function __construct(
        private UnmarshallerInterface       $unmarshaller,
        private LoggerInterface             $logger,
        private HttpMessageFactoryInterface $factory,
        private EntityManagerInterface      $entityManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $events = $this->unmarshaller->unmarshal($this->factory->createRequest($request));
        } catch (InvalidPayloadSyntaxException $exception) {
            throw new BadRequestHttpException('Could not unmarshal Cloud Events request properly: the payload syntax is invalid.', previous: $exception);
        } catch (MissingAttributeException $exception) {
            throw new BadRequestHttpException('Could not unmarshal Cloud Events request properly: a Cloud Events attribute is missing.', previous: $exception);
        } catch (UnsupportedContentTypeException $exception) {
            throw new BadRequestHttpException('Could not unmarshal Cloud Events request properly: the content type is not supported.', previous: $exception);
        } catch (UnsupportedSpecVersionException $exception) {
            throw new BadRequestHttpException('Could not unmarshal Cloud Events request properly: the spec version is not supported.', previous: $exception);
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
        }
        $this->entityManager->flush();

        return new Response(status: Response::HTTP_ACCEPTED);
    }
}
