<?php

namespace Mikemirten\Component\JsonApi\Document;

use Mikemirten\Component\JsonApi\Document\Behaviour\ErrorsAwareInterface;
use Mikemirten\Component\JsonApi\Document\Behaviour\LinksAwareInterface;
use Mikemirten\Component\JsonApi\Document\Behaviour\MetadataAwareInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group document
 */
class SingleResourceDocumentTest extends TestCase
{
    public function testResource()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $this->assertSame($resource, $document->getResource());
    }

    public function testMetadata()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource, ['test' => 42]);

        $this->assertInstanceOf(MetadataAwareInterface::class, $document);

        $this->assertFalse($document->hasMetadataAttribute('qwerty'));
        $this->assertTrue($document->hasMetadataAttribute('test'));
        $this->assertSame(42, $document->getMetadataAttribute('test'));
        $this->assertSame(['test' => 42], $document->getMetadata());
    }

    /**
     * @depends testMetadata
     */
    public function testMetadataRemove()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);
        $document->setMetadataAttribute('test', 42);

        $this->assertTrue($document->hasMetadataAttribute('test'));

        $document->removeMetadataAttribute('test');

        $this->assertFalse($document->hasMetadataAttribute('test'));
    }

    public function testErrors()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);
        $error    = $this->createMock(ErrorObject::class);

        $this->assertInstanceOf(ErrorsAwareInterface::class, $document);
        $this->assertFalse($document->hasErrors());

        $document->addError($error);

        $this->assertTrue($document->hasErrors());
        $this->assertSame([$error], $document->getErrors());
    }

    public function testToArrayErrors()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $error = $this->createMock(ErrorObject::class);

        $error->method('toArray')
            ->willReturn(['test' => '123']);

        $document->addError($error);

        $this->assertSame(
            [['test' => '123']],
            $document->toArray()['errors']
        );
    }

    public function testLinks()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $this->assertInstanceOf(LinksAwareInterface::class, $document);

        $link = $this->createMock(LinkObject::class);
        $document->setLink('test', $link);

        $this->assertFalse($document->hasLink('qwerty'));
        $this->assertTrue($document->hasLink('test'));
        $this->assertSame($link, $document->getLink('test'));
        $this->assertSame(['test' => $link], $document->getLinks());
    }

    public function testLinkRemove()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $link = $this->createMock(LinkObject::class);
        $document->setLink('test', $link);

        $this->assertTrue($document->hasLink('test'));

        $document->removeLink('test');

        $this->assertFalse($document->hasLink('test'));
    }

    public function testToArrayLinks()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $document->setLink('test_link', $this->createLink(
            'http://test_link.com',
            ['test' => 123]
        ));

        $this->assertSame(
            [
                'links' => [
                    'test_link' => [
                        'href' => 'http://test_link.com',
                        'meta' => ['test' => 123]
                    ]
                ],
                'data' => []
            ],
            $document->toArray()
        );
    }

    public function testToArrayMetadata()
    {
        $resource = $this->createMock(ResourceObject::class);

        $relationship = new SingleResourceDocument($resource);
        $relationship->setMetadataAttribute('test', 'qwerty');

        $this->assertSame(
            [
                'meta' => ['test' => 'qwerty'],
                'data' => []
            ],
            $relationship->toArray()
        );
    }

    public function testToArrayResource()
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->expects($this->once())
            ->method('toArray')
            ->willReturn(['test' => 'qwerty']);

        $document = new SingleResourceDocument($resource);

        $this->assertSame(
            [
                'data' => [
                    'test' => 'qwerty'
                ]
            ],
            $document->toArray()
        );
    }

    public function testJsonApi()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);
        $jsonApi  = new JsonApiObject();

        $document->setJsonApi($jsonApi);

        $this->assertSame($jsonApi, $document->getJsonApi());
    }

    public function testJsonApiToArray()
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn([]);

        $document = new SingleResourceDocument($resource);
        $jsonApi  = new JsonApiObject();

        $document->setJsonApi($jsonApi);

        $this->assertSame(
            [
                'jsonapi' => ['version' => '1.0'],
                'data'    => []
            ],
            $document->toArray()
        );
    }

    public function testIncluded()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);
        $resource = $this->createMock(ResourceObject::class);

        $this->assertFalse($document->hasIncludedResources());

        $document->addIncludedResource($resource);

        $this->assertTrue($document->hasIncludedResources());
        $this->assertSame([$resource], $document->getIncludedResources());
    }

    public function testIncludedToArray()
    {
        $resource = $this->createMock(ResourceObject::class);

        $resource->method('toArray')
            ->willReturn([]);

        $document = new SingleResourceDocument($resource);
        $included = $this->createMock(ResourceObject::class);

        $included->method('toArray')
            ->willReturn(['test' => 'qwerty']);

        $document->addIncludedResource($included);

        $this->assertSame(
            [
                'included' => [
                    ['test' => 'qwerty']
                ],
                'data' => []
            ],
            $document->toArray()
        );
    }

    public function createLink(string $reference, array $metadata = []): LinkObject
    {
        $link = $this->createMock(LinkObject::class);

        $link->method('hasMetadata')
            ->willReturn(! empty($metadata));

        $link->method('getMetadata')
            ->willReturn($metadata);

        $link->method('getReference')
            ->willReturn($reference);

        return $link;
    }

    public function testToString()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $this->assertRegExp('~Document~', (string) $document);
    }

    /**
     * @expectedException \Mikemirten\Component\JsonApi\Document\Exception\MetadataAttributeNotFoundException
     *
     * @expectedExceptionMessageRegExp ~Document~
     * @expectedExceptionMessageRegExp ~test_attribute~
     */
    public function testMetadataNotFound()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $document->getMetadataAttribute('test_attribute');
    }

    /**
     * @expectedException \Mikemirten\Component\JsonApi\Document\Exception\MetadataAttributeOverrideException
     *
     * @expectedExceptionMessageRegExp ~Document~
     * @expectedExceptionMessageRegExp ~test_attribute~
     */
    public function testMetadataOverride()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $document->setMetadataAttribute('test_attribute', 1);
        $document->setMetadataAttribute('test_attribute', 2);
    }

    /**
     * @expectedException \Mikemirten\Component\JsonApi\Document\Exception\LinkNotFoundException
     *
     * @expectedExceptionMessageRegExp ~Document~
     * @expectedExceptionMessageRegExp ~test_link~
     */
    public function testLinkNotFound()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $document->getLink('test_link');
    }

    /**
     * @expectedException \Mikemirten\Component\JsonApi\Document\Exception\LinkOverrideException
     *
     * @expectedExceptionMessageRegExp ~Document~
     * @expectedExceptionMessageRegExp ~test_link~
     */
    public function testLinkOverride()
    {
        $resource = $this->createMock(ResourceObject::class);
        $document = new SingleResourceDocument($resource);

        $link = $this->createMock(LinkObject::class);

        $document->setLink('test_link', $link);
        $document->setLink('test_link', $link);
    }
}