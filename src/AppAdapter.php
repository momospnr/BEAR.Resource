<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Throwable;

use function assert;
use function sprintf;
use function str_replace;
use function substr;
use function ucwords;

final class AppAdapter implements AdapterInterface
{
    /** @var InjectorInterface */
    private $injector;

    /**
     * Resource adapter namespace
     *
     * @var string
     */
    private $namespace;

    /**
     * Resource adapter path
     *
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $path;

    /**
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     */
    public function __construct(InjectorInterface $injector, string $namespace)
    {
        $this->injector = $injector;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResourceNotFoundException
     * @throws Unbound
     */
    public function get(AbstractUri $uri): ResourceObject
    {
        if (substr($uri->path, -1) === '/') {
            $uri->path .= 'index';
        }

        $path = str_replace('-', '', ucwords($uri->path, '/-'));
        $class = sprintf('%s%s\Resource\%s', $this->namespace, $this->path, str_replace('/', '\\', ucwords($uri->scheme) . $path));
        try {
            $instance = $this->injector->getInstance($class);
            assert($instance instanceof ResourceObject);
        } catch (Unbound $e) {
            throw $this->getNotFound($uri, $e, $class);
        }

        return $instance;
    }

    /**
     * @return ResourceNotFoundException|Unbound
     */
    private function getNotFound(AbstractUri $uri, Unbound $e, string $class): Throwable
    {
        $unboundClass = $e->getMessage();
        if ($unboundClass === "{$class}-") {
            return new ResourceNotFoundException((string) $uri, 404, $e);
        }

        return $e;
    }
}
