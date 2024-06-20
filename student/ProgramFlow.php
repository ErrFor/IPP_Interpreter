<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for handling program flow instructions
 * @author Slabik Yaroslav (xslabi01)
 */
class ProgramFlow 
{
    private ValueHandling $ValueHandling;
    private LabelManager $labelManager;
    protected OutputWriter $stderr;

    public function __construct(ValueHandling $ValueHandling, LabelManager $labelManager, OutputWriter $stderr) {
        $this->ValueHandling = $ValueHandling;
        $this->labelManager = $labelManager;
        $this->stderr = $stderr;
    }

    /**
     * Handles the LABEL operation.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleLABEL(array $args): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: LABEL expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    }
    
    /**
     * Performs an unconditional jump to a specified label.
     * @param array<array{type: string, value: mixed}> $args
     * @param int $InstructionIndex
     * @return void
     */
    public function handleJUMP(array $args, int &$InstructionIndex): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: JUMP expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    
        $labelName = $args[0]['value']; // Get the label name
    
        $labelPosition = $this->labelManager->getLabelPosition($labelName);
    
        $InstructionIndex = $labelPosition;
    }
    
    /**
     * Conditionally performs a jump to a label if the two symbols are of the same type and their values are equal.
     * @param array<array{type: string, value: mixed}> $args
     * @param int $currentInstructionIndex
     * @return void
     */
    public function handleJUMPIFEQ(array $args, int &$currentInstructionIndex): void {

        if (count($args) != 3) {
            $this->stderr->writeString("Error: JUMPIFEQ expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }
    
        $labelName = $args[0]['value'];
        $symb1 = $this->ValueHandling->resolveValue($args[1]);
        $symb2 = $this->ValueHandling->resolveValue($args[2]);
    
        $typeCheck = ($symb1 === null || $symb2 === null || gettype($symb1) === gettype($symb2));
        if (!$typeCheck) {
            $this->stderr->writeString("Error: JUMPIFEQ expects operands of the same type. Error code: 53\n");
            exit(53);
        }else{
            if ( $symb1 === $symb2)
            {
                    // Get label position from LabelManager
                    $labelPosition = $this->labelManager->getLabelPosition($labelName);
                    // Updating the index of the current instruction for the transition
                    $currentInstructionIndex = $labelPosition - 1;
            }
            else
            {
                // If the values are not equal, simply proceed to the next instruction
                $currentInstructionIndex++;
            }
        }
    }
    
    /**
     * Conditionally performs a jump to a label if the two symbols are of the same type and their values are not equal.
     * @param array<array{type: string, value: mixed}> $args
     * @param int $currentInstructionIndex
     * @return void
     */
    public function handleJUMPIFNEQ(array $args, int &$currentInstructionIndex): void {
        if (count($args) != 3) {
            $this->stderr->writeString("Error: JUMPIFNEQ expects exactly three arguments. Error code: 32.\n");
            exit(32);
        }
    
        $labelName = $args[0]['value'];
        $symb1 = $this->ValueHandling->resolveValue($args[1]);
        $symb2 = $this->ValueHandling->resolveValue($args[2]);
    
        $typeCheck = ($symb1 === null || $symb2 === null || gettype($symb1) === gettype($symb2));
        if (!$typeCheck) {
            $this->stderr->writeString("Error: JUMPIFEQ expects operands of the same type. Error code: 53.\n");
            exit(53);
        }else{
            if ( $symb1 !== $symb2)
            {
                    // Get label position from LabelManager
                    $labelPosition = $this->labelManager->getLabelPosition($labelName);
                    // Updating the index of the current instruction for the transition
                    $currentInstructionIndex = $labelPosition - 1;
            }else
            {
                // If the values are not equal, simply proceed to the next instruction
                $currentInstructionIndex++;
            }
        }
    }
    
    /**
     * Terminates the interpreter execution with a return code specified by the symbol, which must be an integer between 0 and 9.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleEXIT(array $args): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: EXIT expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    
        $exitCode = $this->ValueHandling->resolveValue($args[0]);
    
        // Check that symb is an integer
        if (is_int($exitCode)) {
            // Check that the return code is within the valid range
            if ($exitCode >= 0 && $exitCode <= 9) {
                exit($exitCode);
            } else {
                $this->stderr->writeString("Error: Invalid return code: $exitCode. Must be between 0 and 9. Error code 57.\n");
                exit(57); // Error code for invalid integer value
            }
        } else {
            // If symb is not an integer, we report an error
            $this->stderr->writeString("Error: EXIT argument must be an integer. Error code: 53.\n");
            exit(53); // Error code for an invalid argument type
        }
    }
}