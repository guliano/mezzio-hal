<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator;

use Mezzio\Hal\HalResource;
use Mezzio\Hal\Metadata;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ServerRequestInterface;

class RouteBasedResourceStrategy implements StrategyInterface
{
    use ExtractInstanceTrait;

    public function createResource(
        $instance,
        Metadata\AbstractMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request,
        int $depth = 0
    ) : HalResource {
        if (! $metadata instanceof Metadata\RouteBasedResourceMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forMetadata(
                $metadata,
                self::class,
                Metadata\RouteBasedResourceMetadata::class
            );
        }

        $data = $this->extractInstance(
            $instance,
            $metadata,
            $resourceGenerator,
            $request,
            $depth
        );

        $routeParams        = $metadata->getRouteParams();
        $resourceIdentifier = $metadata->getResourceIdentifier();
        $routeIdentifier    = $metadata->getRouteIdentifierPlaceholder();

        if (isset($data[$resourceIdentifier])) {
            $routeParams[$routeIdentifier] = $data[$resourceIdentifier];
        }

        if ($metadata->hasReachedMaxDepth($depth)) {
            $data = [];
        }

        return new HalResource($data, [
            $resourceGenerator->getLinkGenerator()->fromRoute(
                'self',
                $request,
                $metadata->getRoute(),
                $routeParams
            )
        ]);
    }
}
