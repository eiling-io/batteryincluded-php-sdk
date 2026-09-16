<?php

declare(strict_types=1);

namespace Dto;

use BatteryIncludedSdk\Dto\AbstractDto;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\BlogTranslation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlogBaseDto::class)]
#[CoversClass(AbstractDto::class)]
#[UsesClass(BlogTranslation::class)]
final class BlogDtoTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $dto = new BlogBaseDto('123');

        $dto->setId('123');
        $dto->setTitle('Test Title');
        $dto->setAuthor('Author');
        $dto->setPublishedAt('2024-06-01');
        $dto->setActive(true);
        $dto->setShortDescription('Short desc');
        $dto->setDescription('Long desc');
        $dto->setPreviewImage('image.png');
        $dto->setRelatedArticles('related');
        $dto->setBlogUrl('https://blog.url');

        $this->assertSame('123', $dto->getId());
        $this->assertSame('Test Title', $dto->getTitle());
        $this->assertSame('Author', $dto->getAuthor());
        $this->assertSame('2024-06-01', $dto->getPublishedAt());
        $this->assertTrue($dto->getActive());
        $this->assertSame('Short desc', $dto->getShortDescription());
        $this->assertSame('Long desc', $dto->getDescription());
        $this->assertSame('image.png', $dto->getPreviewImage());
        $this->assertSame('related', $dto->getRelatedArticles());
        $this->assertSame('https://blog.url', $dto->getBlogUrl());
    }

    public function testJsonSerialize(): void
    {
        $dto = new BlogBaseDto('1');
        $dto->setId('1');
        $dto->setTitle('Title');
        $dto->setActive(true);

        $json = $dto->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('_BLOG', $json);
        $this->assertSame([
            'id' => '1',
            'active' => true,
        ], $json['_BLOG']);
        $this->assertSame([
            'de' => ['_BLOG' => ['title' => 'Title']],
        ], $json['_i18n']);
    }

    public function testJsonSerializeExportsNullValuesWhenEnabled(): void
    {
        $dto = new BlogBaseDto('1');
        $dto->setId('1');
        $dto->setTitle('Title');
        $dto->setActive(true);

        $this->assertSame($dto, $dto->exportNullValues());

        $json = $dto->jsonSerialize();

        $this->assertSame([
            'id' => '1',
            'author' => null,
            'publishedAt' => null,
            'active' => true,
            'previewImage' => null,
            'relatedArticles' => null,
            'blogUrl' => null,
        ], $json['_BLOG']);
        $this->assertSame([
            'de' => [
                '_BLOG' => [
                    'title' => 'Title',
                    'shortDescription' => null,
                    'description' => null,
                    'url' => null,
                ],
            ],
        ], $json['_i18n']);
    }

    public function testJsonSerializeRoutesTranslatableFieldsToConfiguredLocale(): void
    {
        $dto = new BlogBaseDto('1');
        $dto->locale('en');
        $dto->setId('1');
        $dto->setTitle('English title');
        $dto->setBlogUrl('https://blog.example/en/post');
        $dto->addTranslation('de', new BlogTranslation(title: 'Deutscher Titel'));

        $json = $dto->jsonSerialize();

        $this->assertSame(['title' => 'English title', 'url' => 'https://blog.example/en/post'], $json['_i18n']['en']['_BLOG']);
        $this->assertSame(['title' => 'Deutscher Titel', 'url' => 'https://blog.example/en/post'], $json['_i18n']['de']['_BLOG']);
    }
}
