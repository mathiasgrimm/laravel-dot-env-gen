<?php


namespace MathiasGrimm\LaravelDotEnvGen;


use Exception;
use Illuminate\Support\Collection;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

class EnvFinder
{
    /**
     * @param string $content
     * @return Collection|EnvDefinition[]
     */
    public function find(string $content): Collection
    {
        $nodes = $this->getNodes($content);
        $envCalls = collect();
        
        foreach ($nodes as $node) {
            $variableName = $this->parseVariableName($node);
            $variableDefault = $this->parseDefaultValue($node);

            $envCalls[] = new EnvDefinition($variableName, $variableDefault);
        }
        
        return $envCalls;
    }

    /**
     * @param string $filePath
     * @return Collection|EnvDefinition[]
     */
    public function findInFile(string $filePath, $basePath = null): Collection
    {
        $basePath = $basePath ?: base_path();
        $envCalls = $this->find(file_get_contents($filePath));
        
        foreach ($envCalls as $envCall) {
            $envCall->file = trim(str_replace($basePath, '', $filePath), '/');
        }
        
        return $envCalls;
    }

    /**
     * @param string $content
     * @return Collection|Node[]
     */
    private function getNodes(string $content): Collection
    {
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $ast = $parser->parse($content);

        $nodeFinder = new NodeFinder();
        return collect($nodeFinder->find($ast, function(Node $node) {
            return $node instanceof \PhpParser\Node\Expr\FuncCall 
                && $node->name instanceof \PhpParser\Node\Name
                && strtolower($node->name) == 'env';
        }));
    }
    
    /**
     * @param Node $node
     * @return string|null
     */
    private function parseVariableName(Node $node)
    {
        return $this->parseNode($node, 0);
    }

    /**
     * @param Node $node
     * @return string|null
     */
    private function parseDefaultValue(Node $node)
    {
        return $this->parseNode($node, 1);
    }
    
    private function parseNode(Node $node, $index)
    {
        if (isset($node->args[$index]) && $node->args[$index]->value instanceof \PhpParser\Node\Scalar) {
            $value = $node->args[$index]->value->value;
            switch (true) {
                case $value === null:
                    $value = 'null';
                    break;
                
                case $value === false:
                    $value = 'false';
                    break;

                case $value === true:
                    $value = 'true';
                    break;
            }
        } elseif (isset($node->args[$index]) && $node->args[$index]->value instanceof \PhpParser\Node\Expr\ConstFetch) {
            $part = strtolower($node->args[$index]->value->name->parts[0]);
            if ($part == 'null' || $part == 'false' || $part == 'true') {
                $value = $part;    
            } else {
                $value = EnvDefinition::VALUE_NOT_SCALAR;
            }
        } elseif(isset($node->args[$index])) {
            $value = EnvDefinition::VALUE_NOT_SCALAR;
        } else {
            $value = null;
        }

        return $value;
    }
}