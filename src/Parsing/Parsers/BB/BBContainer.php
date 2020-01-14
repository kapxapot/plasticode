<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\MapperInterface;
use Plasticode\Parsing\Mappers\ListMapper;
use Plasticode\Parsing\Mappers\QuoteMapper;
use Plasticode\Parsing\Mappers\SpoilerMapper;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class BBContainer
{
    /** @var RendererInterface */
    private $renderer;

    /** @var array */
    private $map = [];

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        
        $this->register('spoiler', new SpoilerMapper());
        $this->register('list', new ListMapper());
        $this->register('quote', new QuoteMapper());
    }

    public function register(string $tag, MapperInterface $mapper) : void
    {
        Assert::notEmpty($tag, 'Tag can\'t be empty.');
        Assert::alnum($tag, 'Tag can contain only alphanumeric characters.');
        Assert::notNull($mapper, 'Mapper can\'t be null.');

        $this->map[$tag] = $mapper;
    }

    private function getMapper(string $tag) : MapperInterface
    {
        Assert::true(
            $this->isKnownTag($tag),
            'No mapper found for BB container tag \'' . $tag . '\''
        );

        return $this->map[$tag];
    }
    
    private function getPattern(string $tag) : string
    {
        return "/\[{$tag}([^\[]*)\](.*)\[\/{$tag}\]/Uis";
    }

    /**
     * Returns registered tags.
     *
     * @return string[]
     */
    public function getTags() : array
    {
        return array_keys($this->map);
    }

    public function isKnownTag(string $tag) : bool
    {
        return array_key_exists($tag, $this->map);
    }
    
    public function parseAttributes(string $str) : array
    {
        $attrsStr = trim($str, ' |=');
        $attrs = preg_split('/\|/', $attrsStr, -1, PREG_SPLIT_NO_EMPTY);
        
        return $attrs;
    }
    
    private function parseMatches(array $matches) : array
    {
        if (!empty($matches)) {
            $content = Text::trimBrs($matches[2]);
            $attrs = $this->parseAttributes($matches[1]);
        }
            
        return [
            'content' => $content ?? 'parse error',
            'attrs' => $attrs ?? [],
        ];
    }
    
    private function parse(string $text, string $containerName, MapperInterface $mapper, ?\Closure $enrich = null, string $componentName = null) : string
    {
        $componentName = $componentName ?? $containerName;
        
        return preg_replace_callback(
            $this->getPattern($containerName),
            function ($matches) use ($componentName, $mapper, $enrich) {
                $parsed = $this->parseMatches($matches);
                $data = $mapper->map($parsed['content'], $parsed['attrs']);
                
                if ($enrich) {
                    $data = $enrich($data);
                }

                return $this->renderer->component($componentName, $data);
            },
            $text
        );
    }
    
    private function renderNode(array $node) : string
    {
        $tag = $node['tag'];
        $mapper = $this->getMapper($tag);

        return $this->renderNodeComponent($tag, $node, $mapper);
    }
    
    private function renderNodeComponent(string $componentName, array $node, MapperInterface $mapper) : string
    {
        return $this->renderer->component(
            $componentName,
            $mapper->map($node['text'], $node['attributes'])
        );
    }
}
