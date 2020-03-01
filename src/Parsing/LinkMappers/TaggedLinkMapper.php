<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Parsing\Interfaces\TaggedLinkMapperInterface;
use Plasticode\Parsing\LinkMappers\Traits\Tagged;

/**
 * Link mapper with tag.
 */
abstract class TaggedLinkMapper extends SlugLinkMapper implements TaggedLinkMapperInterface
{
    use Tagged;
}
