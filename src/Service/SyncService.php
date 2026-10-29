<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Dto\AbstractDto;
use BatteryIncludedSdk\Exception\InvalidDocumentException;

class SyncService extends AbstractService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function syncOneOrManyElements(AbstractDto ...$dto): Response
    {
        $json = $this->generateNDJSON($dto);

        return $this->apiClient->postNDJson('/documents/import', $json);
    }

    /**
     * not included elements will be deleted from the index
     * @throws InvalidDocumentException
     */
    public function syncFullElements(AbstractDto ...$dto): Response
    {
        $json = $this->generateNDJSON($dto);

        return $this->apiClient->postNDJson('/documents/import?full=1', $json);
    }

    /**
     * you send several batches with the identical transactionId
     * after all products are synced you need to finish your batch sync with $finished = true
     * and the same transactionId from the beginning
     * @param string $transactionId a unique identifier used to group multiple related batch updates
     * @param bool $finished true for the last batch, drops all documents that are not part of the transaction
     * @throws InvalidDocumentException
     */
    public function syncFullBatchElements(string $transactionId, bool $finished = false, AbstractDto ...$dto): Response
    {
        $json = $this->generateNDJSON($dto);
        $parameters = ['transactionId' => $transactionId];

        if ($finished) {
            $parameters['transactionCompleted'] = 1;
        }

        return $this->apiClient->postNDJson('/documents/import?' . http_build_query($parameters), $json);
    }

    public function partialUpdateOneOrManyElements(AbstractDto ...$dto): Response
    {
        $json = $this->generateNDJSON($dto);

        return $this->apiClient->patchNDJson('/documents/update', $json);
    }

    public function deleteElementsByIds(string ...$elementIds): Response
    {
        return $this->apiClient->deleteJson('/documents/delete', json_encode(array_values($elementIds), self::JSON_FLAGS));
    }
}
