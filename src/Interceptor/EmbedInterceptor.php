<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Exception\Uri;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\NamedArgsInterface;
use BEAR\Resource\Exception\Embed as EmbedException;
use BEAR\Resource\Exception\ResourceNotFound;

use BEAR\Resource\Exception\LogicException;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

final class EmbedInterceptor implements MethodInterceptor
{
    const LINK_ANNOTATION = 'BEAR\Resource\Annotation\Link';

    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * @param ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(ResourceInterface $resource, AnnotationReader $reader, NamedArgsInterface $namedArgs)
    {
        $this->resource = $resource;
        $this->reader = $reader;
        $this->namedArgs = $namedArgs;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {

        $result =  $invocation->proceed();

        $resourceObject = $invocation->getThis();
        $method = $invocation->getMethod();
        $query = $this->namedArgs->get($invocation);

        $embeds = $this->reader->getMethodAnnotations($method);
        foreach ($embeds as $embed) {
            /** @var $embed Embed */
            if (! $embed instanceof Embed) {
                continue;
            }
            try {
                $uri = \GuzzleHttp\uri_template($embed->src, $query);
                $resourceObject->body[$embed->rel] = $this->resource->get->uri($uri)->request();
            } catch (Uri $e) {
                throw new EmbedException($embed->src, 500, $e);
            } catch (ResourceNotFound $e) {
                throw new EmbedException($embed->src, 500, $e);
        }
        }
        return $result;
    }
}