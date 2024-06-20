<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;
use IPP\Core\Interface\InputReader;

/**
 * Class for handling input and output instructions
 * @author Slabik Yaroslav (xslabi01)
 */
class InputOutput 
{
    protected ValueHandling $ValueHandling;
    protected OutputWriter $stderr;
    protected OutputWriter $stdout;
    protected InputReader $input;

    public function __construct(ValueHandling $ValueHandling, OutputWriter $stderr, OutputWriter $stdout, InputReader $input) {
        $this->ValueHandling = $ValueHandling;
        $this->stderr = $stderr;
        $this->stdout = $stdout;
        $this->input = $input;
    }

    /**
     * Reads a value from standard input according to the specified type and stores it in a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleREAD(array $args): void {
        if (count($args) != 2) {
            $this->stderr->writeString("Error: READ expects exactly two arguments. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: READ expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $varName = $args[0]['value'];
        $type = $args[1]['value'];
    
        // Reading values using CORE classes
        switch ($type) {
            case 'int':
                $value = $this->input->readInt();
                break;
            case 'bool':
                $value = $this->input->readBool();
                break;
            case 'string':
                $value = $this->input->readString();
                break;
            case 'nil':
                $value = 'nil';
                break;    
            default:
                $this->stderr->writeString("Error: Unknown type for READ operation: $type. Error code: 32.\n");
                exit(32);
        }

        if ($value === null) {
            $value = 'nil';
        }
    
        $this->ValueHandling->setVariableValue($varName, $value);
    }
    
    /**
     * Outputs a value to standard output, formatting it according to its type.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleWRITE(array $args): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: WRITE expects exactly one argument. Error code: 32.\n");
            exit(32);
        }

        $symbValue = $this->ValueHandling->resolveValue($args[0]);
        
        if ($symbValue === null) {
            $this->stderr->writeString("Error: Attempting to write null value. Error code: 56.\n");
            exit(56);
        } elseif (is_bool($symbValue)) { // Boolean value processing
            $formattedValue = $symbValue ? 'true' : 'false';
        }elseif ($symbValue === 'nil') { // Processing nil
            $formattedValue = '';
        }elseif ($symbValue === 'nil@nil'){ //Processing nil@nil
            $formattedValue = 'nil';
        } else { // All other values are output as they are
            $formattedValue = (string)$symbValue ? $this->decodeEscapedSequences($symbValue) : $symbValue;
        }
    
        // Output the value to standard output
        $this->stdout->writeString($formattedValue);
    }

    /**
     * Decodes escaped sequences in a string.
     * @param string $string The string to decode.
     * @return string The decoded string.
     */
    public function decodeEscapedSequences(string $string): string {
        return preg_replace_callback('/\\\\(\d{3})/', function ($matches) {
            return mb_chr(intval($matches[1]), 'UTF-8');
        }, $string);
    }
}