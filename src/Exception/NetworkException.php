<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Exception;

/**
 * The api could not be reached: dns, connection, tls or timeout.
 * Error responses of the api (4xx/5xx) are no exception, check `Response::isSuccessful()`.
 */
class NetworkException extends BatteryIncludedException
{
}
