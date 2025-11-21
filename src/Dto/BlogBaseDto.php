<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

class BlogBaseDto extends AbstractDto
{
    private ?string $id = null;

    private ?string $title = null;

    private ?string $author = null;

    private ?string $publishedAt = null;

    private ?bool $active = null;

    private ?string $shortDescription = null;

    private ?string $description = null;

    private ?string $previewImage = null;

    private ?string $relatedArticles = null;

    private ?string $blogUrl = null;

    public function __construct(string $identifier, string $type = 'BLOG')
    {
        parent::__construct($identifier, $type);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): void
    {
        $this->author = $author;
    }

    public function getPublishedAt(): ?string
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?string $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPreviewImage(): ?string
    {
        return $this->previewImage;
    }

    public function setPreviewImage(?string $previewImage): void
    {
        $this->previewImage = $previewImage;
    }

    public function getRelatedArticles(): ?string
    {
        return $this->relatedArticles;
    }

    public function setRelatedArticles(?string $relatedArticles): void
    {
        $this->relatedArticles = $relatedArticles;
    }

    public function getBlogUrl(): ?string
    {
        return $this->blogUrl;
    }

    public function setBlogUrl(?string $blogUrl): void
    {
        $this->blogUrl = $blogUrl;
    }

    public function jsonSerialize(): array
    {
        $jsonDto = [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'author' => $this->getAuthor(),
            'publishedAt' => $this->getPublishedAt(),
            'active' => $this->getActive(),
            'shortDescription' => $this->getShortDescription(),
            'description' => $this->getDescription(),
            'previewImage' => $this->getPreviewImage(),
            'relatedArticles' => $this->getRelatedArticles(),
            'blogUrl' => $this->getBlogUrl(),
        ];

        $jsonRaw = array_merge(
            parent::jsonSerialize(),
            ['_' . $this->getType() => array_filter($jsonDto, static fn ($value) => $value !== null)]
        );

        return $jsonRaw;
    }
}
