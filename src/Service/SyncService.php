<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

use BatteryIncludedSdk\Product\ProductBaseDto;

class SyncService extends AbstractService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function syncOneOrManyProducts(ProductBaseDto ...$products): Response
    {
        $json = $this->generateNDJSON($products);

        return $this->apiClient->postNDJson('/documents/import', $json);
    }

    /**
     * not included products will be deleted from the index
     * @param ProductBaseDto ...$products
     * @return Response
     * @throws \Exception
     */
    public function syncFull(ProductBaseDto ...$products): Response
    {
        $json = $this->generateNDJSON($products);

        return $this->apiClient->postNDJson('/documents/import?full=1', $json);
    }

    /**
     * you send several batches with the identical transactionId
     * after all products are synced you need to finish your batch sync with $finished = true
     * and the same transactionId from the beginning
     * @param string $transactionId A unique identifier used to group multiple related batch updates.
     * @param bool $finished If 1, drop all data that is not related to the given transactionId
     * @param ProductBaseDto ...$products
     * @return Response
     * @throws \Exception
     */
    public function syncFullBatch(string $transactionId, bool $finished = false, ProductBaseDto ...$products): Response
    {
        $json = $this->generateNDJSON($products);
        $apiUrl = '/documents/import?transactionId=' . $transactionId;

        if ($finished) {
            return $this->apiClient->postNDJson($apiUrl . '&transactionCompleted=1', $json);
        }

        return $this->apiClient->postNDJson($apiUrl, $json);
    }

    public function partialUpdateOneOrManyProducts(ProductBaseDto ...$products): Response
    {
        $json = $this->generateNDJSON($products);

        return $this->apiClient->patchNDJson('/documents/update', $json);
    }

    public function deleteProductsByIds(string ...$productIds): Response
    {
        return $this->apiClient->deleteJson('/documents/delete', json_encode($productIds));
    }
}