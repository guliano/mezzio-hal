<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\ResourceGenerator;

use Countable;
use Laminas\Paginator\Paginator;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\Link;
use Mezzio\Hal\LinkGenerator;
use Mezzio\Hal\Metadata\AbstractCollectionMetadata;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

trait ExtractCollection
{
    private $paginationTypes = [
        AbstractCollectionMetadata::TYPE_PLACEHOLDER,
        AbstractCollectionMetadata::TYPE_QUERY,
    ];

    abstract protected function generateLinkForPage(
        string $rel,
        int $page,
        AbstractCollectionMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : Link;

    abstract protected function generateSelfLink(
        AbstractCollectionMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : Link;

    private function extractCollection(
        Traversable $collection,
        AbstractCollectionMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : HalResource {
        if (! $metadata instanceof AbstractCollectionMetadata) {
            throw Exception\UnexpectedMetadataTypeException::forCollection($metadata, get_class($this));
        }

        if ($collection instanceof Paginator) {
            return $this->extractPaginator($collection, $metadata, $resourceGenerator, $request);
        }

        return $this->extractIterator($collection, $metadata, $resourceGenerator, $request);
    }

    private function extractPaginator(
        Paginator $collection,
        AbstractCollectionMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : HalResource {
        $data  = ['_total_items' => $collection->getTotalItemCount()];
        $links = [];

        $paginationParamType = $metadata->getPaginationParamType();
        if (in_array($paginationParamType, $this->paginationTypes, true)) {
            // Supports pagination
            $pageCount = $collection->count();

            $paginationParam = $metadata->getPaginationParam();
            $page = $paginationParamType === AbstractCollectionMetadata::TYPE_QUERY
                ? ($request->getQueryParams()[$paginationParam] ?? 1)
                : $request->getAttribute($paginationParam, 1);

            $collection->setCurrentPageNumber($page);

            $links[] = $this->generateLinkForPage('self', $page, $metadata, $resourceGenerator, $request);
            if ($page > 1) {
                $links[] = $this->generateLinkForPage('first', 1, $metadata, $resourceGenerator, $request);
                $links[] = $this->generateLinkForPage('prev', $page - 1, $metadata, $resourceGenerator, $request);
            }
            if ($page < $pageCount) {
                $links[] = $this->generateLinkForPage('next', $page + 1, $metadata, $resourceGenerator, $request);
                $links[] = $this->generateLinkForPage('last', $pageCount, $metadata, $resourceGenerator, $request);
            }

            $data['_page'] = $page;
            $data['_page_count'] = $pageCount;
        }

        if (empty($links)) {
            $links[] = $this->generateSelfLink($metadata, $resourceGenerator, $request);
        }

        $resources = [];
        foreach ($collection as $item) {
            $resources[] = $resourceGenerator->fromObject($item, $request);
        }

        return new HalResource($data, $links, [
            $metadata->getCollectionRelation() => $resources,
        ]);
    }

    private function extractIterator(
        Traversable $collection,
        AbstractCollectionMetadata $metadata,
        ResourceGenerator $resourceGenerator,
        ServerRequestInterface $request
    ) : HalResource {
        $isCountable = $collection instanceof Countable;
        $count = $isCountable ? $collection->count() : 0;

        $resources = [];
        foreach ($collection as $item) {
            $resources[] = $resourceGenerator->fromObject($item, $request);
            $count = $isCountable ? $count : $count + 1;
        }

        $data = ['_total_items' => $count];
        $links = [$this->generateSelfLink(
            $metadata,
            $resourceGenerator,
            $request
        )];

        return new HalResource($data, $links, [
            $metadata->getCollectionRelation() => $resources,
        ]);
    }
}
