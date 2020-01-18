<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Collection;
use Plasticode\Config\Interfaces\BBContainerConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\MapperInterface;
use Plasticode\Parsing\Parsers\BB\Traits\BBAttributeParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class BBContainerParser extends BaseStep
{
    use BBAttributeParser;

    /** @var RendererInterface */
    protected $renderer;

    /** @var array */
    private $map = [];

    public function __construct(BBContainerConfigInterface $config, RendererInterface $renderer)
    {
        $tagMappers = $config->getMappers();

        foreach ($tagMappers as $tag => $mapper) {
            $this->register($tag, $mapper);
        }

        $this->renderer = $renderer;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        if ($context->isEmpty()) {
            return $context;
        }

        $sequence = $this->getSequence($context->text);
        $containerTree = $this->buildTree($sequence);
        $context->text = $this->renderTree($containerTree);
        
        return $context;
    }

    public function register(string $tag, MapperInterface $mapper) : void
    {
        Assert::notEmpty($tag);
        Assert::alnum($tag);
        Assert::notNull($mapper);

        $this->map[$tag] = $mapper;
    }

    /**
     * Returns registered tags.
     *
     * @return string[]
     */
    private function getTags() : array
    {
        return array_keys($this->map);
    }

    /**
     * Splits text into sequence of starting tags, ending tags and text.
     */
    private function getSequence(string $text) : array
    {
        $ctags = $this->getTags();
        
        if (empty($ctags)) {
            return [$text];
        }
        
        $ctagsStr = implode('|', $ctags);
        
        $parts = preg_split(
            '/(\[\/?(?:' . $ctagsStr . ')[^\[]*\])/Ui', $text, -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        
        $parts = array_map(
            function ($part) {
                return Text::trimBrs($part);
            },
            $parts
        );
        
        $sequence = [];
        
        foreach ($parts as $part) {
            if (preg_match('/\[(' . $ctagsStr . ')([^\[]*)\]/Ui', $part, $matches)) {
                // bb container start
                $tag = $matches[1];
                $attrs = $this->parseAttributes($matches[2]);
                
                $sequence[] = [
                    'type' => 'start',
                    'tag' => $tag,
                    'attributes' => $attrs,
                ];
            } elseif (preg_match('/\[\/(' . $ctagsStr . ')\]/Ui', $part, $matches)) {
                // bb container end
                $tag = $matches[1];

                $sequence[] = [
                    'type' => 'end',
                    'tag' => $tag,
                ];
            } elseif (strlen($part) > 0) {
                $sequence[] = $part;
            }
        }

        return $sequence;
    }

    /**
     * Builds container tree based on parts sequence.
     */
    private function buildTree(array $sequence) : array
    {
        $tree = [];
        $nodes = Collection::makeEmpty();
        
        $consume = function ($part) use (&$nodes, &$tree) {
            $node = $nodes->last();

            if (is_null($node)) {
                $tree[] = $part;
            }

            $node->addContent($part);
        };

        foreach ($sequence as $part) {
            if (!is_array($part)) {
                $consume($part);
            }

            switch ($part['type']) {
                case 'start':
                    $node = new BBNode(
                        $part['tag'],
                        $part['attributes']
                    );
    
                    $nodes = $nodes->add($node);

                    break;
                
                case 'end':
                    // matching node?
                    $node = $nodes->last();

                    // no matching node - leave as is
                    if (is_null($node) || $node->tag != $part['tag']) {
                        $consume($part);
                    }

                    // consume node
                    $nodes = $nodes->pop();
                    $consume($node);

                    break;
            }
        }
        
        return $tree;
    }

    /**
     * Renders container tree.
     */
    private function renderTree(array $tree) : string
    {
        $parts = [];

        foreach ($tree as $part) {
            if ($part instanceof BBNode) {
                $part->text = $this->renderTree($part->content);
                $parts[] = $this->renderNode($part);
            } else {
                $parts[] = $this->renderer->text($part);
            }
        }
        
        return implode(Text::BrBr, $parts);
    }
    
    private function renderNode(BBNode $node) : string
    {
        $tag = $node->tag;
        $mapper = $this->getMapper($tag);

        return $this->renderer->component(
            $tag,
            $mapper->map($node->text, $node->attributes)
        );
    }

    private function getMapper(string $tag) : MapperInterface
    {
        Assert::true(
            $this->isKnownTag($tag),
            'No mapper found for BB container tag \'' . $tag . '\''
        );

        return $this->map[$tag];
    }

    public function isKnownTag(string $tag) : bool
    {
        return array_key_exists($tag, $this->map);
    }
}
