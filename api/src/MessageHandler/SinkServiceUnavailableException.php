<?php

declare(strict_types=1);

namespace App\MessageHandler;

final class SinkServiceUnavailableException extends \RuntimeException implements CloudEventsExceptionInterface
{
}
