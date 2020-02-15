<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\Parsers\BB\Traits\BBAttributeParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Text;

class BBParser extends BaseStep
{
    use BBAttributeParser;

    /** @var TagMapperSourceInterface */
    private $config;

    /** @var RendererInterface */
    private $renderer;

    public function __construct(TagMapperSourceInterface $config, RendererInterface $renderer)
    {
        $this->config = $config;
        $this->renderer = $renderer;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $tags = $this->config->getTags();

        foreach ($tags as $tag) {
            $context = $this->parseTag($tag, $context);
        }

        return $context;
    }

    private function parseTag(string $tag, ParsingContext $context) : ParsingContext
    {
        $mapper = $this->config->getMapper($tag);
        $componentName = $this->config->getComponentName($tag);

        $context->text = preg_replace_callback(
            $this->getTagPattern($tag),
            function ($matches) use ($tag, $mapper, $componentName, $context) {
                $tagNode = $this->parseTagMatches($tag, $matches);
                $viewContext = $mapper->map($tagNode, $context);
                $context = $viewContext->context();

                return $this->renderer->component(
                    $componentName,
                    $viewContext->model()
                );
            },
            $context->text
        );

        return $context;
    }
    
    private function getTagPattern(string $tag) : string
    {
        return "/\[{$tag}([^\[]*)\](.*)\[\/{$tag}\]/Uis";
    }
    
    private function parseTagMatches(string $tag, array $matches) : TagNode
    {
        /** @var string[] */
        $attrs = [];
        $content = '';

        if (count($matches) > 1) {
            $attrs = $this->parseAttributes($matches[1]);
        }

        if (count($matches) > 2) {
            $content = Text::trimBrs($matches[2]);
        }
        
        return new TagNode($tag, $attrs, $content);
    }
}
