<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\MapperSourceInterface;
use Plasticode\Util\Text;

class BBTreeRenderer
{
    /** @var RendererInterface */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Renders container tree.
     */
    public function render(array $tree, MapperSourceInterface $mapperSource) : string
    {
        $parts = [];

        foreach ($tree as $part) {
            if ($part instanceof BBNode) {
                $part->text = $this->render($part->content, $mapperSource);
                $parts[] = $this->renderNode($part, $mapperSource);
            } else {
                $parts[] = $this->renderer->text($part);
            }
        }
        
        return implode(Text::BrBr, $parts);
    }
    
    private function renderNode(BBNode $node, MapperSourceInterface $mapperSource) : string
    {
        $tag = $node->tag;
        $mapper = $mapperSource->getMapper($tag);

        return $this->renderer->component(
            $tag,
            $mapper->map($node->text, $node->attributes)
        );
    }
}
