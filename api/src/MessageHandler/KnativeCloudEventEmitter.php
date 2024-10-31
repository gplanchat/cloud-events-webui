<?php

declare(strict_types=1);

namespace App\MessageHandler;

use ArrayIterator;
use CloudEvents\Exceptions\InvalidPayloadSyntaxException;
use CloudEvents\Exceptions\MissingAttributeException;
use CloudEvents\Exceptions\UnsupportedContentTypeException;
use CloudEvents\Exceptions\UnsupportedSpecVersionException;
use CloudEvents\Http\MarshallerInterface;
use CloudEvents\Http\UnmarshallerInterface;
use CloudEvents\V1\CloudEventImmutable;
use Monolog\Attribute\WithMonologChannel;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[WithMonologChannel('knative')]
final class KnativeCloudEventEmitter
{
    public function __construct(
        private readonly MarshallerInterface $marshaller,
        private readonly UnmarshallerInterface $unmarshaller,
        private readonly LoggerInterface $logger,
        private readonly Psr18Client $httpClient,
        private readonly Psr18Client $unsecuredHttpClient,
    ) {
    }

    public function emitOne(
        CloudEventImmutable $event,
        UriInterface $sinkUri,
        string $method = 'POST',
        bool $verifyPeer = true,
        ?string $bearerToken = null,
    ): \Traversable {
        $request = $this->marshaller->marshalBinaryRequest($event, $method, (string) $sinkUri);

        try {
            if ($verifyPeer) {
                $response = $this->httpClient->withOptions([
                    'auth_bearer' => $bearerToken,
                ])->sendRequest($request);
            } else {
                $response = $this->unsecuredHttpClient->withOptions([
                    'auth_bearer' => $bearerToken,
                ])->sendRequest($request);
            }

            if (!$response->hasHeader('Content-Type')
                || intval($response->getHeaderLine('Content-Length')) <= 0
                || intval($response->getHeaderLine('content-length')) <= 0) {
                return new \EmptyIterator();
            }
        } catch (NetworkExceptionInterface $exception) {
            $this->logger->emergency($exception->getMessage(), ['exception' => $exception]);
            throw new SinkServiceUnavailableException('The CloudEvents sink is not reachable or a network error happened. Please check the logs in order to get more details about the error. This can be a temporary failure of a 3rd-party system.', previous: $exception);
        } catch (RequestExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            throw new InvalidRequestException('Your CloudEvents request to the sink failed. you may need to check the contents of your events that may not have been properly encode.', previous: $exception);
        } catch (ClientExceptionInterface $exception) {
            $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
            throw new CloudEventsClientException('Your CloudEvents client failed in an unanticipated manner. Please check the logs in order to get more details about the error.', previous: $exception);
        }

        if (!in_array($response->getStatusCode(), [200, 201, 202])) {
            throw new PayloadDecodingFailureException($response->getReasonPhrase());
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

        return new ArrayIterator($events);
    }
}
