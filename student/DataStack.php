<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for working with data stack
 * @author Slabik Yaroslav (xslabi01)
 */
class DataStack 
{
    protected OutputWriter $stderr;
    protected ValueHandling $ValueHandling;

    public function __construct(ValueHandling $valueHandling, OutputWriter $stderr) {
        $this->stderr = $stderr;
        $this->ValueHandling = $valueHandling;
    }

    /** @var int[] An array to hold instruction indexes for call stack */
    protected array $dataStack = [];

    /**
     * Saves the value to the data stack.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handlePUSHS(array $args): void {
        if (count($args) != 1) {
            // Report an error if the number of arguments does not match the expected number of arguments
            $this->stderr->writeString("Error: PUSHS expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    
        // Get the character value (operand) to be placed on the stack
        $value = $this->ValueHandling->resolveSymbolValue($args[0]);
    
        // Add a value to the top of the stack
        array_push($this->dataStack, $value);
    }

    /**
     * Retrieves a value from the stack and stores it in a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handlePOPS(array $args): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: POPS expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    
        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: POPS expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        if (empty($this->dataStack)) {
            // Data stack is empty, report an error
            $this->stderr->writeString("Error: Attempting to POP from an empty data stack. Error code: 56.\n");
            exit(56);
        }
    
        // Retrieve the value from the top of the stack
        $value = array_pop($this->dataStack);
    
        // Get the name of the variable to save the value to
        $varName = $args[0]['value'];
    
        // Save the value to a variable
        $this->ValueHandling->setVariableValue($varName, $value);
    }
}