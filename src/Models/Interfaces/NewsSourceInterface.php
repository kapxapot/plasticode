<?php

namespace Plasticode\Models\Interfaces;

use Plasticode\Models\User;
use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\TaggedInterface;

interface NewsSourceInterface extends DbModelInterface, LinkableInterface, TaggedInterface
{
    function largeImage() : ?string;
    function image() : ?string;
    function video() : ?string;

    function displayTitle() : string;

    function hasText() : bool;

    function fullText() : ?string;
    function shortText() : ?string;

    function publishedAtIso() : string;

    function creator() : ?User;

    function tagLinks() : TagLinkCollection;
}
