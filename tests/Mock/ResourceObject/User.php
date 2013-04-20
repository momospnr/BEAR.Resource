<?php

namespace testworld\ResourceObject;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\ResourceInterface;

use BEAR\Resource\Annotation\Provides;
use BEAR\Resource\Annotation\ParamSignal;

use Ray\Di\Di\Scope;

/**
 * @Scope("singleton")
 */
class User extends AbstractObject
{
    public $headers = [
        'x-header-test' => '123'
    ];

    /**
     *
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    private $users = array(
        array('id' => 1, 'name' => 'Athos', 'age' => 15, 'blog_id' => 11),
        array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12),
        array('id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0)
    );

    /**
     * @param $id
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function onGet($id)
    {
        if (!isset($this->users[$id])) {
            throw new \InvalidArgumentException($id);
        }

        return $this->users[$id];
    }

    public function onPost($id, $name = 'default_name', $age = 99)
    {
        return "post user[{$id} {$name} {$age}]";
    }

    /**
     * @param string $noProvide
     */
    public function onPut($noProvide)
    {
        //return "put user[{$id} {$name} {$age}]";
        return "$noProvide";
    }

    /**
     * @param $delete_id
     *
     * @return string
     * @ParamSignal("login_id")
     */
    public function onDelete($delete_id)
    {
        return "{$delete_id} deleted";
    }

    public function onLinkBlog(ResourceObject $resource)
    {
        return $this->resource->get->uri('app://self/Blog')->withQuery(['id' => $resource->body['blog_id']])->request();
    }

    /**
     * @Provides("id")
     */
    public function provideId()
    {
        return 1;
    }

    /**
     * @Provides("name")
     */
    public function provideName()
    {
        return "koriym";
    }
}
