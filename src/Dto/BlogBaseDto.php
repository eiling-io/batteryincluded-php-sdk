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

    /**
     * Adds (or replaces) a translation for the locale it carries (BlogTranslation::getLocale()).
     * The locale reached by the flat setters (setTitle(), setShortDescription(), setDescription())
     * is controlled via locale().
     */
    public function addTranslation(BlogTranslation $translation): void
    {
        $this->storeTranslation($translation);
    }

    public function jsonSerialize(): array
    {
        $primary = new BlogTranslation(
            $this->activeLocale(),
            $this->getTitle(),
            $this->getShortDescription(),
            $this->getDescription(),
        );

        $jsonDto = [
            'id' => $this->getId(),
            'author' => $this->getAuthor(),
            'publishedAt' => $this->getPublishedAt(),
            'active' => $this->getActive(),
            'previewImage' => $this->getPreviewImage(),
            'relatedArticles' => $this->getRelatedArticles(),
            'blogUrl' => $this->getBlogUrl(),
        ];

        return array_merge(
            parent::jsonSerialize(),
            [
                '_i18n' => $this->buildI18n($primary, $this->blogUrl),
                '_' . $this->getType() => $this->filterJsonValues($jsonDto),
            ]
        );
    }
}
