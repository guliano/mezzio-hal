<?php

/**
 * @see       https://github.com/mezzio/mezzio-hal for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-hal/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-hal/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Hal\Metadata;

interface MetadataInterface
{
    /**
     * Returns the configured metadata class name
     *
     * @return string
     */
    public function getClass() : string;

    /**
     * Determines whenever the current depth level exceeds the allowed max depth
     *
     * @param int $currentDepth
     *
     * @return bool
     */
    public function hasReachedMaxDepth(int $currentDepth): bool;
}
