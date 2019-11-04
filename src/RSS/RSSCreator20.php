<?php

namespace Plasticode\RSS;

use Psr\Http\Message\ResponseInterface;

/**
 * RSSCreator20 is a FeedCreator that implements RDF Site Summary (RSS) 2.0.
 *
 * @see http://backend.userland.com/rss
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class RSSCreator20 extends RSSCreator091
{
    function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
        parent::_setRSSVersion("2.0");
    }
}
