<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

class BlogBaseDto extends AbstractTranslatableDto
{
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

    protected function getTranslatableFields(): ?array
    {
        return [
            'title',
            'author',
            'publishedAt',
            'active',
            'shortDescription',
            'description',
            'previewImage',
            'relatedArticles',
            'blogUrl',
        ];
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getPublishedAt(): ?string
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?string $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPreviewImage(): ?string
    {
        return $this->previewImage;
    }

    public function setPreviewImage(?string $previewImage): static
    {
        $this->previewImage = $previewImage;

        return $this;
    }

    public function getRelatedArticles(): ?string
    {
        return $this->relatedArticles;
    }

    public function setRelatedArticles(?string $relatedArticles): static
    {
        $this->relatedArticles = $relatedArticles;

        return $this;
    }

    public function getBlogUrl(): ?string
    {
        return $this->blogUrl;
    }

    public function setBlogUrl(?string $blogUrl): static
    {
        $this->blogUrl = $blogUrl;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $jsonDto = [
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
            ['_' . $this->getType() => $this->filterJsonValues($jsonDto)]
        );

        return $jsonRaw;
    }
}
