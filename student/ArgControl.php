<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;


/**
 * Class for handling arguments
 * @author Slabik Yaroslav (xslabi01)
 */
class ArgControl 
{
    private OutputWriter $stderr;

    public function __construct(OutputWriter $stderr) {
        $this->stderr = $stderr;
    }

    /**
     * Receives an instruction and returns an array of arguments.
     * @param \DOMNode $instruction
     * @return array<array{type: string, value: mixed}>
     */
    public function getArgs(\DOMNode $instruction): array {
        $tempArgs = [];
        $args = [];
    
        // go through all child elements of the instruction
        foreach ($instruction->childNodes as $child) {
            if ($child instanceof \DOMElement && strpos($child->tagName, 'arg') === 0) {
                $argNum = intval(str_replace('arg', '', $child->tagName));

                if ($argNum < 1) {
                    $this->stderr->writeString("Error: Invalid argument number '{$child->tagName}'. Error code: 32.\n");
                    exit(32);
                }

                if (array_key_exists($argNum, $tempArgs)) {
                    $this->stderr->writeString("Error: Duplicate argument number '{$child->tagName}' detected. Error code: 32.\n");
                    exit(32);
                }

                // separate the argument type and its values
                $type = $child->getAttribute('type');
                $value = trim($child->nodeValue);
                // convert the type and value of the argument according to the requirements
                switch ($type) {
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'bool':
                        $value = $value === 'true';
                        break;
                    case 'nil':
                        $value = 'nil';
                        break;    
                    case null:
                        $this->stderr->writeString("Error: Missing type attribute for argument '{$child->tagName}'. Error code: 32.\n");
                        exit(32);        
                }
    
                // add an argument to the array
                $tempArgs[$argNum] = ['type' => $type, 'value' => $value];
            }else if ($child instanceof \DOMElement) {
                // If an element is found that is not a valid argument
                $this->stderr->writeString("Error: Unexpected element '{$child->nodeName}' found inside 'instruction'. Error code: 32.\n");
                exit(32);
            }
        }
    
        ksort($tempArgs);
        foreach ($tempArgs as $arg) {
            $args[] = $arg;
        }
        
        return $args;
    }
}