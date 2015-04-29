<?php

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Author;

class ResourceObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testTransfer()
    {
        $resourceObject = new FakeResource;
        $responder = new FakeResponder;
        $resourceObject->transfer($responder, []);
        $this->assertSame(FakeResource::class, $responder->class);
    }

    public function testSerialize()
    {
        $ro = new FakeFreeze;
        $ro->uri = new Uri('app://self/freeze');
        $serialized = serialize($ro);
        $this->assertInternalType('string', $serialized);
        $expected = 'app://self/freeze';
        $this->assertSame($expected, $ro->uri);
        $expected = 'O:24:"BEAR\Resource\FakeFreeze":5:{s:3:"uri";s:17:"app://self/freeze";s:4:"code";i:201;s:7:"headers";a:0:{}s:4:"body";a:2:{s:3:"php";s:1:"7";s:4:"user";O:38:"FakeVendor\Sandbox\Resource\App\Author":5:{s:3:"uri";s:22:"app://self/author?id=1";s:4:"code";i:200;s:7:"headers";a:0:{}s:4:"body";a:3:{s:4:"name";s:6:"Aramis";s:3:"age";i:16;s:7:"blog_id";i:12;}s:4:"view";N;}}s:4:"view";N;}';
        $this->assertSame($expected, $serialized);
        $resourceObject = unserialize($serialized);
        $this->assertInstanceOf(Author::class, $resourceObject['user']);
    }
}
