<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for debugging instructions
 * @author Slabik Yaroslav (xslabi01)
 */
class Debugging 
{
    private MemoryModel $memoryModel;
    private ValueHandling $ValueHandling;
    protected OutputWriter $stderr;

    public function __construct(MemoryModel $memoryModel, ValueHandling $ValueHandling, OutputWriter $stderr) {
        $this->memoryModel = $memoryModel;
        $this->ValueHandling = $ValueHandling;
        $this->stderr = $stderr;
    }

    /**
     * Prints the specified value to the standard error output.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleDPRINT(array $args): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: DPRINT expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    
        $symb = $this->ValueHandling->resolveValue($args[0]);
    
        // Formatting the value for output depending on its type
        $output = '';
        switch ($symb['type']) {
            case 'int':
            case 'string':
                $output = $symb['value'];
                break;
            case 'bool':
                $output = $symb['value'] ? 'true' : 'false';
                break;
            case 'nil':
                $output = 'nil';
                break;
            default:
                $output = "Unknown type: {$symb['type']}";
                break;
        }
    
        // Output value to stderr
        $this->stderr->writeString($output . "\n");
    }

    /**
     * Prints the current state of the interpreter to the standard error output.
     * @param int &$InstructionIndex Index of the current instruction, passed by reference.
     * @param int &$InstructionCount Total number of executed instructions, passed by reference.
     * @return void
     */
    public function handleBREAK(int &$InstructionIndex, int &$InstructionCount): void {
        $gf = $this->memoryModel->getGF();
        $tf = $this->memoryModel->getTF();
        $this->stderr->writeString("---- Debug Info ----\n");
        $this->stderr->writeString("Current Instruction Index: " . $InstructionIndex . "\n");
        $this->stderr->writeString("Total Executed Instructions: " . $InstructionCount . "\n");
        $this->stderr->writeString("Local Frame Stack Size: " . count($this->memoryModel->getLFStack()) . "\n");
        $this->stderr->writeString("Global Frame Variables: " . count($gf->variables) . "\n");
        if ($tf !== null && isset($tf->variables)) {
            $this->stderr->writeString("Temporary Frame Variables: " . count($tf->variables) . "\n");
        } else {
            $this->stderr->writeString("Temporary Frame Variables: 0\n");
        }
        $this->stderr->writeString("---- End of Debug Info ----\n");
    }
}