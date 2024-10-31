<?php

declare(strict_types=1);

namespace App\MessageHandler;

final class InvalidRequestException extends \RuntimeException implements CloudEventsExceptionInterface
{
}
