<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Dto\AbstractDto;

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
     * @throws \Exception
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
     * @param bool $finished If 1, drop all data that is not related to the given transactionId
     * @throws \Exception
     */
    public function syncFullBatchElements(string $transactionId, bool $finished = false, AbstractDto ...$dto): Response
    {
        $json = $this->generateNDJSON($dto);
        $apiUrl = '/documents/import?transactionId=' . $transactionId;

        if ($finished) {
            return $this->apiClient->postNDJson($apiUrl . '&transactionCompleted=1', $json);
        }

        return $this->apiClient->postNDJson($apiUrl, $json);
    }

    public function partialUpdateOneOrManyElements(AbstractDto ...$dto): Response
    {
        $json = $this->generateNDJSON($dto);

        return $this->apiClient->patchNDJson('/documents/update', $json);
    }

    public function deleteElementsByIds(string ...$elementIds): Response
    {
        return $this->apiClient->deleteJson('/documents/delete', json_encode($elementIds));
    }
}
