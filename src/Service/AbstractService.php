<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

use BatteryIncludedSdk\Dto\AbstractDto;
use BatteryIncludedSdk\Exception\InvalidDocumentException;

abstract class AbstractService
{
    /**
     * Invalid UTF-8 of shop data is replaced instead of aborting a whole import, everything else that cannot be
     * encoded (NAN, INF) throws. Without these flags json_encode() returned false and sent an empty line.
     */
    protected const JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * @throws InvalidDocumentException
     */
    public function generateNDJSON(array $data): string
    {
        $jsonRaw = array_map(static function ($value, $key) {
            try {
                return json_encode($value, self::JSON_FLAGS);
            } catch (\JsonException $exception) {
                $document = $value instanceof AbstractDto ? $value->getIdentifier() : '#' . $key;

                throw new InvalidDocumentException(sprintf('Document %s cannot be encoded: %s', $document, $exception->getMessage()), 0, $exception);
            }
        }, $data, array_keys($data));

        return "\n" . implode("\n", $jsonRaw) . "\n";
    }
}
