<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop\Extension;

class RedirectsExtension extends AbstractExtension
{
    public function getType(): string
    {
        return 'redirects';
    }
}
