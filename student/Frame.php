<?php

namespace IPP\Student;

/**
 * Class for working with frames
 * @author Slabik Yaroslav (xslabi01)
 * @property array<string, mixed> $variables
 */
class Frame 
{
    /** @var array<string, mixed> Variables container */
    public array $variables = [];

    /**
     * Defines a variable in the frame.
     * 
     * @param string $name The name of the variable.
     * @param mixed $value The value of the variable.
     * @return void
     */
    public function defineVariable(string $name, mixed $value): void {
        $this->variables[$name] = $value;
    }

    /**
     * Gets the value of a variable in the frame.
     * @param string $name The name of the variable.
     * @return mixed The value of the variable.
     */
    public function getVariableInFrame(string $name): mixed {
        if (!array_key_exists($name, $this->variables)) {
            exit(54);
        }
        return $this->variables[$name];
    }

    /**
     * Verifies if a variable exists in the frame.
     * @param string $name The name of the variable.
     * @return bool True if the variable exists, false otherwise.
     */
    public function variableExists(string $name): bool {
        return array_key_exists($name, $this->variables);
    }

    /**
     * Clears all variables in the frame.
     * @return void
     */
    public function clearVariables(): void {
        $this->variables = []; // Clearing all variables
    }
}