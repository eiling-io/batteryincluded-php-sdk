<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Exception;

/**
 * A document cannot be encoded as json, e.g. a price of NAN or INF.
 */
class InvalidDocumentException extends BatteryIncludedException
{
}
