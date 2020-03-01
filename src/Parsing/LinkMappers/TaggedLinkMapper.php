<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Parsing\Interfaces\TaggedLinkMapperInterface;
use Plasticode\Parsing\LinkMappers\Traits\Tagged;
use Webmozart\Assert\Assert;

/**
 * Link mapper with tag.
 */
abstract class TaggedLinkMapper extends SlugLinkMapper implements TaggedLinkMapperInterface
{
    use Tagged;

    public function __construct()
    {
        Assert::notEmpty($this->tag(), 'Tag is not specified.');
        Assert::alnum($this->tag());
    }
}
