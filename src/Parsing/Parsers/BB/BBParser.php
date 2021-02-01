<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\Parsers\BB\Traits\BBAttributeParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Parsing\ViewContext;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class BBParser extends BaseStep
{
    use BBAttributeParser;

    private BBParserConfig $config;
    private RendererInterface $renderer;

    public function __construct(
        BBParserConfig $config,
        RendererInterface $renderer
    )
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
        $tagPattern = self::getTagPattern($tag);
        $text = $context->text;

        $parsedText = preg_replace_callback(
            $tagPattern,
            function (array $matches) use ($tag, &$context) {
                return $this->parseTagMatches($tag, $matches, $context);
            },
            $text
        );

        $context->text = $parsedText;

        return $context;
    }

    private static function getTagPattern(string $tag) : string
    {
        return "/\[{$tag}([^\[]*)\](.*)\[\/{$tag}\]/Uis";
    }

    private function parseTagMatches(
        string $tag,
        array $matches,
        ParsingContext &$context
    ) : string
    {
        $viewContext = $this->mapToViewContext($tag, $matches, $context);
        $context = $viewContext->parsingContext();
        $componentName = $this->config->getComponentName($tag);

        return $this->render($componentName, $viewContext);
    }

    private function render(string $componentName, ViewContext $context) : string
    {
        return $this->renderer->component(
            $componentName,
            $context->model()
        );
    }

    private function mapToViewContext(
        string $tag,
        array $matches,
        ParsingContext $context
    ) : ViewContext
    {
        $mapper = $this->config->getMapper($tag);

        Assert::notNull($mapper, 'No tag mapper found for tag ' . $tag);

        $tagNode = self::matchesToNode($tag, $matches);

        return $mapper->map($tagNode, $context);
    }

    private static function matchesToNode(string $tag, array $matches) : TagNode
    {
        /** @var string[] */
        $attrs = [];
        $content = '';

        if (count($matches) > 1) {
            $attrs = self::parseAttributes($matches[1]);
        }

        if (count($matches) > 2) {
            $content = Text::trimNewLinesAndBrs($matches[2]);
        }

        return new TagNode($tag, $attrs, $content);
    }
}
