<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\IlligalAccessException;
use FakeVendor\Sandbox\Resource\App\Author;
use PHPUnit\Framework\TestCase;

use function count;
use function json_encode;
use function serialize;
use function unserialize;

class ResourceObjectTest extends TestCase
{
    public function testTransfer(): void
    {
        $ro = new FakeResource();
        $responder = new FakeResponder();
        $ro->transfer($responder, []);
        $this->assertSame(FakeResource::class, $responder->class);
    }

    public function testSerialize(): void
    {
        $ro = new FakeFreeze();
        $ro->uri = new Uri('app://self/freeze');
        $body = $ro->body;
        $serialized = serialize($ro);
        $this->assertIsString($serialized);
        $ro = unserialize($serialized);
        $this->assertInstanceOf(Author::class, $ro['user']);
        $expected = 'app://self/freeze';
        $this->assertSame($expected, (string) $ro->uri);
    }

    public function testJson(): void
    {
        $ro = new FakeFreeze();
        $ro->uri = new Uri('app://self/freeze');
        $json = json_encode($ro);
        $this->assertIsString($json);
        $expected = '{"php":"7","user":{"name":"Aramis","age":16,"blog_id":12}}';
        $this->assertSame($expected, $json);
    }

    /**
     * @covers \BEAR\Resource\ResourceObject::toString()
     */
    public function testViewCached(): void
    {
        $ro = new FakeResource();
        $view = '1';
        $ro->view = $view;
        $ro->body = ['key' => 'val'];
        $this->assertSame($view, $ro->toString());
    }

    /**
     * @covers \BEAR\Resource\ResourceObject::count()
     */
    public function testIlligalAccessExceptionInCount(): void
    {
        $this->expectException(IlligalAccessException::class);
        $ro = new FakeResource();
        $ro->body = '1';
        count($ro); // @phpstan-ignore-line
    }

    public function testEvaluationInSleep(): void
    {
        $ro = new FakeResource();
        $ro->body['req'] = new NullRequest();
        $wakeup = unserialize(serialize($ro));
        $this->assertSame(null, $wakeup->body['req']->body);
    }

    /**
     * @covers \BEAR\Resource\ResourceObject::offsetExists()
     */
    public function testIlligalAccessExceptionInOffsetExists(): void
    {
        $this->expectException(IlligalAccessException::class);
        $ro = new FakeResource();
        $ro->body = '1';
        isset($ro['key']); // @phpstan-ignore-line
    }

    /**
     * @covers \BEAR\Resource\ResourceObject::offsetGet()
     */
    public function testIlligalAccessExceptionInOffsetGet(): void
    {
        $this->expectException(IlligalAccessException::class);
        $ro = new FakeResource();
        $ro->body = '1';
        $ro['key']; // @phpstan-ignore-line
    }
}
