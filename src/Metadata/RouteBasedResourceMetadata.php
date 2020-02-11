<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

class RouteBasedResourceMetadata extends AbstractResourceMetadata
{
    /** @var string */
    private $resourceIdentifier;

    /** @var string */
    private $route;

    /** @var string */
    private $routeIdentifierPlaceholder;

    /** @var array */
    private $routeParams;

    /** @var int */
    private $maxDepth;

    public function __construct(
        string $class,
        string $route,
        string $extractor,
        string $resourceIdentifier = 'id',
        string $routeIdentifierPlaceholder = 'id',
        array $routeParams = [],
        int $maxDepth = 10
    ) {
        $this->class = $class;
        $this->route = $route;
        $this->extractor = $extractor;
        $this->resourceIdentifier = $resourceIdentifier;
        $this->routeIdentifierPlaceholder = $routeIdentifierPlaceholder;
        $this->routeParams = $routeParams;
        $this->maxDepth = $maxDepth;
    }

    public function getRoute() : string
    {
        return $this->route;
    }

    public function getResourceIdentifier() : string
    {
        return $this->resourceIdentifier;
    }

    public function getRouteIdentifierPlaceholder() : string
    {
        return $this->routeIdentifierPlaceholder;
    }

    public function getRouteParams() : array
    {
        return $this->routeParams;
    }

    public function setRouteParams(array $routeParams) : void
    {
        $this->routeParams = $routeParams;
    }

    public function hasReachedMaxDepth(int $currentDepth): bool
    {
        return $currentDepth > $this->maxDepth;
    }
}
