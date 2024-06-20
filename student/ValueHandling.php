<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for handling value for instructions.
 * @author Slabik Yaroslav (xslabi01)
 */
class ValueHandling 
{
    private MemoryModel $memoryModel;
    protected OutputWriter $stderr;

    public function __construct(MemoryModel $memoryModel, OutputWriter $stderr) {
        $this->memoryModel = $memoryModel;
        $this->stderr = $stderr;
    }

    /**
     * Gets the value of a variable.
     * @param string $fullName The full name of the variable.
     * @return mixed The value of the variable.
     */
    public function getVariableValue(string $fullName): mixed {
        $parts = explode('@', $fullName, 2);
        if(count($parts) !== 2) {
            $this->stderr->writeString("Error: Invalid variable format: $fullName. Error code: 52.\n");
            exit(52);
        }
        [$framePrefix, $varName] = $parts;
        $value = $this->memoryModel->getVariable($framePrefix, $varName);

        return $value;
    }
    
    /**
     * Sets the value of a variable.
     * @param string $fullName The full name of the variable.
     * @param mixed $value The value to set.
     * @return void
     */
    public function setVariableValue(string $fullName, mixed $value): void {
        // Split the full variable name into a prefix and the name itself
        list($framePrefix, $varName) = explode('@', $fullName, 2);

        $this->memoryModel->setVariable($framePrefix, $varName, $value);
    }

    /**
     * Resolves the value of a symbol.
     * @param array{type: string, value: mixed} $symbol The symbol to resolve.
     * @return mixed The resolved value of the symbol.
     */
    public function resolveSymbolValue(array $symbol): mixed {
        if ($symbol['type'] === 'var') {
            // If the character is a variable, we get its value
            return $this->getVariableValue($symbol['value']);
        } else {
            // Otherwise, return the literal value of the symbol
            return $symbol['value'];
        }
    }

    /**
     * Tries to resolve the given argument to an integer value, if possible.
     * @param array{type: string, value: mixed} $arg The argument to resolve.
     * @return int|null The resolved integer value or null if resolution is not possible.
     */
    public function resolveIntValue(array $arg): ?int {
        // If the argument is a variable, we get its value
        if ($arg['type'] === 'var') {
            $value = $this->getVariableValue($arg['value']);
        } else {
            // Otherwise, we use the direct value
            $value = $arg['value'];
        }
    
        // Type conversion to integer
        if (is_int($value)) {
            return $value;
        } else {
            $this->stderr->writeString("Error: Value cannot be resolved to a integer. Error code: 53.\n");
            exit(53);
        }
    }

    /**
     * Resolves the given argument to a boolean value if possible.
     * @param array{type: string, value: mixed} $arg The argument to resolve.
     * @return bool The resolved boolean value or string.
     */
    public function resolveBooleanValue(array $arg): bool {
        // If the argument is a variable, retrieve its value
        if ($arg['type'] === 'var') {
            $value = $this->getVariableValue($arg['value']);
        } else {
            // If the argument is a direct value, we use it as is
            $value = $arg['value'];
        }
    
        // Convert the string representation to a boolean value
        if (is_bool($value)) {
            return $value;
        } else {
            $this->stderr->writeString("Error: Value cannot be resolved to a boolean. Error code: 53.\n");
            exit(53);
        }
    }

    /**
     * Resolves a value to a string if possible.
     * @param array{type: string, value: mixed} $arg The argument to resolve.
     * @return string|null The resolved string value or null if resolution is not possible.
     */
    public function resolveStringValue(array $arg): ?string {
        if ($arg['type'] === 'var') {
            $value = $this->getVariableValue($arg['value']);
        } else {
            $value = $arg['value'];
        }
    
        if (!is_string($value)) {
            $this->stderr->writeString("Error: Expected string value. Error code: 53.\n");
            exit(53);
        }
    
        return $value;
    }

    /**
     * Resolves the given argument.
     * @param array{type: string, value: mixed} $arg The argument to resolve.
     * @return mixed The resolved value.
     */
    public function resolveValue(array $arg): mixed {
        // If the argument is a reference to a variable
        if ($arg['type'] === 'var') {
            $fullName = $arg['value'];
            $value = $this->getVariableValue($fullName);
        } else {
            // If the argument is an immediate value
            switch ($arg['type']) {
                case 'int':
                    $value = intval($arg['value']);
                    break;
                case 'bool':
                    if ($arg['value'] === true || $arg['value'] === '1') {
                        $value = true;
                    } elseif ($arg['value'] === false || $arg['value'] === '0') {
                        $value = false;
                    } else {
                        $this->stderr->writeString("Error: Invalid boolean value: {$arg['value']}. Error code: 32.\n");
                        exit(32);
                    }
                    break;
                case 'string':
                    $value = $arg['value'];
                    break;
                case 'nil':
                    $value = 'nil';
                    break;
                default:
                    $this->stderr->writeString("Error: Unknown type: {$arg['type']} for argument {$arg['value']}. Error code: 32.\n");
                    exit(32);
            }
        }
        return $value;
    }
}