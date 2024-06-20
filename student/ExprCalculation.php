<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;
/**
 * Class for handling expressions and calculations
 * @author Slabik Yaroslav (xslabi01)
 */
class ExprCalculation 
{
    private ValueHandling $ValueHandling;
    protected OutputWriter $stderr;

    public function __construct(ValueHandling $ValueHandling, OutputWriter $stderr) {
        $this->ValueHandling = $ValueHandling;
        $this->stderr = $stderr;
    }

    /**
     * Handles the ADD operation.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleADD(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: ADD expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }
    
        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: ADD expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        // Parsing and verification of arguments
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveIntValue($args[1]);
        $symb2Value = $this->ValueHandling->resolveIntValue($args[2]);
    
        // Type check
        if (!is_int($symb1Value) || !is_int($symb2Value)) {
            $this->stderr->writeString("Error: Both operands for ADD must be integers. Error code: 53.\n");
            exit(53);
        }
    
        // Performing an operation and saving the result
        $result = $symb1Value + $symb2Value;
        $this->ValueHandling->setVariableValue($varName, $result);
    }
    
    /**
     * Handles the SUB operation.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleSUB(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: SUB expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: SUB expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        // Parsing and verification of arguments
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveIntValue($args[1]);
        $symb2Value = $this->ValueHandling->resolveIntValue($args[2]);
    
        // Type check
        if (!is_int($symb1Value) || !is_int($symb2Value)) {
            $this->stderr->writeString("Error: Both operands for SUB must be integers. Error code: 53.\n");
            exit(53);
        }
    
        // Performing an operation and saving the result
        $result = $symb1Value - $symb2Value;
        $this->ValueHandling->setVariableValue($varName, $result);
    }

    /**
     * Handles the MUL operation.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleMUL(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: MUL expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: MUL expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        // Parsing and verification of arguments
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveIntValue($args[1]);
        $symb2Value = $this->ValueHandling->resolveIntValue($args[2]);
    
        // Type check
        if (!is_int($symb1Value) || !is_int($symb2Value)) {
            $this->stderr->writeString("Error: Both operands for MUL must be integers. Error code: 53.\n");
            exit(53);
        }
    
        // Performing an operation and saving the result
        $result = $symb1Value * $symb2Value;
        $this->ValueHandling->setVariableValue($varName, $result);
    }

    /**
     * Handles the IDIV operation.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleIDIV(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: IDIV expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: IDIV expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        // Parsing and verification of arguments
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveIntValue($args[1]);
        $symb2Value = $this->ValueHandling->resolveIntValue($args[2]);
    
        // Type check
        if (!is_int($symb1Value) || !is_int($symb2Value)) {
            $this->stderr->writeString("Error: Both operands for IDIV must be integers. Error code: 53.\n");
            exit(53);
        }
    
        // Checking division by zero
        if ($symb2Value === 0) {
            $this->stderr->writeString("Error: Division by zero. Error code: 57.\n");
            exit(57);
        }
    
        // Performing an operation and saving the result
        $result = intdiv($symb1Value, $symb2Value);
        $this->ValueHandling->setVariableValue($varName, $result);
    }
    
    /**
     * Handles a comparison operation.
     * @param array<array{type: string, value: mixed}> $args Arguments for the operation.
     * @param string $operation The operation to perform.
     * @return void
     */
    public function handleComparison(array $args, string $operation): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: $operation expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: $operation expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
        
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveValue($args[1]);
        $symb2Value = $this->ValueHandling->resolveValue($args[2]);

        // Check for nil for EQ
        if ($operation == "EQ" && ($symb1Value === 'nil' || $symb2Value === 'nil')) {
            $result = $symb1Value === $symb2Value;
        } elseif ($symb1Value === 'nil' || $symb2Value === 'nil') {
            $this->stderr->writeString("Error: Operation $operation cannot be applied to nil except for EQ. Error code: 53.\n");
            exit(53);
        } else {
            if (gettype($symb1Value) !== gettype($symb2Value)) {
                $this->stderr->writeString("Error: Type mismatch: operands must be of the same type. Error code: 53.\n");
                exit(53);
            }
            switch ($operation) {
                case 'LT':
                    $result = $symb1Value < $symb2Value;
                    break;
                case 'GT':
                    $result = $symb1Value > $symb2Value;
                    break;
                case 'EQ':
                    $result = $symb1Value == $symb2Value;
                    break;
                default:
                    $this->stderr->writeString("Error: Unknown comparison operation: $operation. Error code: 53.\n");
                    exit(53);
            }
        }
    
        $this->ValueHandling->setVariableValue($varName, $result);
    }
    
    /**
     * Handles the NOT operation.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleNOT(array $args): void {
        if (count($args) != 2) {
            $this->stderr->writeString("Error: NOT expects exactly two arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: NOT expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveBooleanValue($args[1]);
    
        // Performing a NOT operation
        $result = !$symb1Value;
        $this->ValueHandling->setVariableValue($varName, $result);
    }
    
    /**
     * Handles a boolean operation,
     * except NOT, since instructions have different numbers of arguments they work with.
     * @param array<array{type: string, value: mixed}> $args Arguments for the operation.
     * @param string $operation The boolean operation to perform.
     * @return void
     */
    public function handleBooleanOperation(array $args, string $operation): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: $operation expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: $operation expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $varName = $args[0]['value'];
        $symb1Value = $this->ValueHandling->resolveBooleanValue($args[1]);
        $symb2Value = $this->ValueHandling->resolveBooleanValue($args[2]);
    
        // Performing the corresponding operation
        switch ($operation) {
            case 'AND':
                $result = $symb1Value && $symb2Value;
                break;
            case 'OR':
                $result = $symb1Value || $symb2Value;
                break;
            default:
                $this->stderr->writeString("Error: Unknown boolean operation: $operation. Error code: 53.\n");
                exit(53);
        }
    
        $this->ValueHandling->setVariableValue($varName, $result);
    }
    
    /**
     * Converts an integer value to a character based on Unicode and stores it in a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleINT2CHAR(array $args): void {
        if (count($args) != 2) {
            $this->stderr->writeString("Error: INT2CHAR expects exactly two arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: INT2CHAR expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $varName = $args[0]['value'];
        $symbValue = $this->ValueHandling->resolveIntValue($args[1]);
    
        // Checking whether the value is valid
        if ($symbValue < 0 || $symbValue > 0x10FFFF) { // Range of valid Unicode values
            $this->stderr->writeString("Error: Value out of Unicode range. Error code: 58.\n");
            exit(58);
        }
    
        /** @var string $character */
        $character = mb_chr($symbValue, 'UTF-8');
    
        if ($character == false) { 
            $this->stderr->writeString("Error: Invalid Unicode value. Error code: 58.\n");
            exit(58);
        }

        // Saving the result
        $this->ValueHandling->setVariableValue($varName, $character);
    }
    
    /**
     * Retrieves the Unicode code point of a character at a specified position in a string and stores it as an integer in a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleSTRI2INT(array $args): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: STRI2INT expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: STRI2INT expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $varName = $args[0]['value'];
        $string = $this->ValueHandling->resolveStringValue($args[1]);
        $index = $this->ValueHandling->resolveIntValue($args[2]);
    
        if($string === 'nil'){
            $this->stderr->writeString("Error: Attempting to access nil value. Error code: 53.\n");
            exit(53);
        }

        // Checking index validity
        if ($index < 0 || $index >= mb_strlen($string, 'UTF-8')) {
            $this->stderr->writeString("Error: Index out of string bounds. Error code: 58.\n");
            exit(58);
        }
    
        // Retrieving a character by index and converting it to Unicode code
        $character = mb_substr($string, $index, 1, 'UTF-8');
        
        /** @var string $character */
        $ordValue = mb_ord($character, 'UTF-8');

        if ($character == false) {
            $this->stderr->writeString("Error: Invalid Unicode value. Error code: 58.\n");
            exit(58);
        }

        // Saving the result результата
        $this->ValueHandling->setVariableValue($varName, $ordValue);
    }
    
}