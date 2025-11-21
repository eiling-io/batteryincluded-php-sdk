<?php

declare(strict_types=1);

namespace Dto;

use BatteryIncludedSdk\Dto\AbstractDto;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlogBaseDto::class)]
#[CoversClass(AbstractDto::class)]
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
            'title' => 'Title',
            'active' => true,
        ], $json['_BLOG']);
    }
}
