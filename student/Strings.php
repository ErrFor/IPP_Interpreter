<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for handling string instructions
 * @author Slabik Yaroslav (xslabi01)
 */
class Strings 
{
    private ValueHandling $ValueHandling;
    protected OutputWriter $stderr;

    public function __construct(ValueHandling $ValueHandling, OutputWriter $stderr) {
        $this->ValueHandling = $ValueHandling;
        $this->stderr = $stderr;
    }

    /**
     * Concatenates two strings and stores the result in a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleCONCAT(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: CONCAT expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }
    
        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: CONCAT expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        // Retrieve and check operand values
        $varName = $args[0]['value'];

        // Resolve operands values
        $firstOperand = $this->ValueHandling->resolveStringValue($args[1]);
        $secondOperand = $this->ValueHandling->resolveStringValue($args[2]);
    
        if ($firstOperand === 'nil' || $secondOperand === 'nil') {
            $this->stderr->writeString("Error: Cannot concatenate nil. Error code: 53.\n");
            exit(53);
        }

        // Perform string concatenation
        $result = $firstOperand . $secondOperand;
    
        // Save the result in a variable
        $this->ValueHandling->setVariableValue($varName, $result);
    }
    
    /**
     * Measures the length of a string and stores the result as an integer.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleSTRLEN(array $args): void {
        if (count($args) != 2) {
            $this->stderr->writeString("Error: STRLEN expects exactly two arguments. Error code: 32.\n");
            exit(32);
        }
    
        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: STRLEN expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        // Retrieve and check operand values
        $varName = $args[0]['value'];
        $stringOperand = $this->ValueHandling->resolveStringValue($args[1]);
    
        if ($stringOperand === 'nil') {
            $this->stderr->writeString("Error: Cannot get length of nil. Error code: 53.\n");
            exit(53);
        }

        // Calculate the length of the string
        $length = mb_strlen($stringOperand, 'UTF-8');
    
        // Save the result in a variable
        $this->ValueHandling->setVariableValue($varName, $length);
    }
    
    /**
     * Retrieves a character from a string at a specified position and stores it in a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleGETCHAR(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: GETCHAR expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }
    
        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: GETCHAR expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        $varName = $args[0]['value'];
        $stringOperand = $this->ValueHandling->resolveStringValue($args[1]);
        $position = $this->ValueHandling->resolveIntValue($args[2]);
        
        if ($stringOperand === 'nil') {
            $this->stderr->writeString("Error: Cannot get character from nil. Error code: 53.\n");
            exit(53);
        }
        
        // Check that the index is within the permissible limits
        if ($position < 0 || $position >= mb_strlen($stringOperand, 'UTF-8')) {
            $this->stderr->writeString("Error: Index out of bounds. Error code: 58.\n");
            exit(58);
        }
    
        // Extract a character from a string
        $char = mb_substr($stringOperand, $position, 1, 'UTF-8');
    
        // Save the character in a variable
        $this->ValueHandling->setVariableValue($varName, $char);
    }
    
    /**
     * Modifies a character in a string at a specified position.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleSETCHAR(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: SETCHAR expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: SETCHAR expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $varName = $args[0]['value'];
        $position = $this->ValueHandling->resolveIntValue($args[1]);
        $newChar = $this->ValueHandling->resolveStringValue($args[2]);
        
        if ($newChar === 'nil') {
            $this->stderr->writeString("Error: Cannot set nil as a character. Error code: 53.\n");
            exit(53);
        }

        // Get the current value of the variable
        $currentValue = $this->ValueHandling->getVariableValue($varName);
    
        // Check that currentValue is a string
        if (!is_string($currentValue)) {
            $this->stderr->writeString("Error: Variable $varName is not a string. Error code: 58.\n");
            exit(58);
        }
    
        // Check if the position is valid and if there are characters in newChar
        if ($position < 0 || $position >= mb_strlen($currentValue, 'UTF-8') || mb_strlen($newChar, 'UTF-8') < 1) {
            $this->stderr->writeString("Error: Index out of bounds or new character string is empty. Error code: 58.\n");
            exit(58);
        }
    
        // Replace the character in the string
        $modifiedValue = mb_substr($currentValue, 0, $position, 'UTF-8') .
                         mb_substr($newChar, 0, 1, 'UTF-8') .
                         mb_substr($currentValue, $position + 1, mb_strlen($currentValue, 'UTF-8'), 'UTF-8');
    
        // Save the changed value back to the variable
        $this->ValueHandling->setVariableValue($varName, $modifiedValue);
    }
}