<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

use BatteryIncludedSdk\Product\ProductBaseDto;

class SyncService extends AbstractService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function syncProducts(ProductBaseDto ...$products): Response
    {
        $json = $this->generateNDJSON($products);

        return $this->apiClient->post('/documents/import', $json);
    }
}