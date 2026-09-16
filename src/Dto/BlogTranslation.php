<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

final class BlogTranslation extends AbstractTranslation
{
    public function __construct(
        string $locale,
        private ?string $title = null,
        private ?string $shortDescription = null,
        private ?string $description = null,
        private ?string $url = null,
    ) {
        parent::__construct($locale);
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'shortDescription' => $this->shortDescription,
            'description' => $this->description,
            'url' => $this->url,
        ];
    }
}
