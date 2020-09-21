<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\Node;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

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
     * 
     * @param Node[] $tree
     * @param TagMapperSourceInterface $mapperSource
     * @return string
     */
    public function render(array $tree, TagMapperSourceInterface $mapperSource) : string
    {
        $parts = [];

        foreach ($tree as $node) {
            if ($node instanceof TagNode) {
                $node->setText(
                    $this->render($node->children(), $mapperSource)
                );

                $parts[] = $this->renderNode($node, $mapperSource);
                continue;
            }

            $parts[] = $this->renderer->text($node->text());
        }

        return implode(Text::BR_BR, $parts);
    }

    private function renderNode(TagNode $node, TagMapperSourceInterface $mapperSource) : string
    {
        $tag = $node->tag();
        $componentName = $mapperSource->getComponentName($tag);
        $mapper = $mapperSource->getMapper($tag);

        Assert::notNull($mapper, 'No BB container tag mapper found for tag ' . $tag);

        $viewContext = $mapper->map($node);

        return $this->renderer->component($componentName, $viewContext->model());
    }
}
