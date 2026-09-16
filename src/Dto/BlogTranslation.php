<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

final class BlogTranslation
{
    public function __construct(
        private ?string $title = null,
        private ?string $shortDescription = null,
        private ?string $description = null,
        private ?string $url = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
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
