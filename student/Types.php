<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for handling type instructions
 * @author Slabik Yaroslav (xslabi01)
 */
class Types 
{
    private ValueHandling $ValueHandling;
    protected OutputWriter $stderr;

    public function __construct(ValueHandling $ValueHandling, OutputWriter $stderr) {
        $this->ValueHandling = $ValueHandling;
        $this->stderr = $stderr;
    }

    /**
     * Determines the type of a symbol and writes it to a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleTYPE(array $args): void {
        if (count($args) != 2) {
            $this->stderr->writeString("Error: TYPE expects exactly two arguments. Error code: 32.\n");
            exit(32);
        }
    
        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: TYPE expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        $varName = $args[0]['value'];
        $symb = $args[1];
    
        // Initialize the symbol type as an empty string
        $typeString = '';
    
        // Determine the character type
        if ($symb['type'] === 'var') {
            // If symb is a variable, check it for initialization
           
                $value = $this->ValueHandling->getVariableValue($symb['value']);
                // Determine the type of variable value
                if (is_int($value)) {
                    $typeString = 'int';
                } elseif (is_bool($value)) {
                    $typeString = 'bool';
                } elseif ($value == 'nil') {
                    $typeString = 'nil@nil';                
                } elseif (is_string($value)) {
                    $typeString = 'string';
                } 
        } else {
            // If symb is a direct value
            switch ($symb['type']) {
                case 'int':
                    $typeString = 'int';
                    break;
                case 'bool':
                    $typeString = 'bool';
                    break;
                case 'string':
                    $typeString = 'string';
                    break;
                case 'nil':
                    $typeString = 'nil@nil';
                    break;
            }
        }

        // Save the string representation of the type in a variable
        $this->ValueHandling->setVariableValue($varName, $typeString);
    }
}