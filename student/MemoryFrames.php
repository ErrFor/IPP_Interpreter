<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for working with memory frames, calling functions
 * @author Slabik Yaroslav (xslabi01)
 */
class MemoryFrames 
{
    private MemoryModel $memoryModel;
    private LabelManager $labelManager;
    protected OutputWriter $stderr;
    
    /** @var int[] An array to hold instruction indexes for call stack */
    protected array $callStack = [];

    public function __construct(MemoryModel $memoryModel, LabelManager $labelManager, OutputWriter $stderr) {
        $this->memoryModel = $memoryModel;
        $this->labelManager = $labelManager;
        $this->stderr = $stderr;
    }

    /**
     * Assigns the value to a variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleMOVE(array $args): void {
        if (count($args) != 2) {
            $this->stderr->writeString("Error: Invalid number of arguments for MOVE. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: MOVE expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }

        // Get the name of the variable to be copied to and the source of the value
        $varName = $args[0]['value'];
        $sourceValue = $args[1]['value'];
    
        // Defining the type of the value source: literal value or variable
        $sourceType = $args[1]['type'];
        if ($sourceType === 'var') {
            // If the source is a variable, we get its value
            $sourceValue = $this->getVariableValue($sourceValue);
        }

        if($sourceValue === 'nil' && $sourceType !== 'nil') {
            // If the source value is nil, assign null to the variable
            $sourceValue = 'nil@nil';
        }

        // Otherwise, $sourceValue already contains a literal value
        // Assign a value to the variable
        $this->setVariableValue($varName, $sourceValue);
    }
    
    /**
     * Getting value of variable.
     * @param string $fullName.
     * @return mixed value of variable.
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
     * Setting value of variable.
     * @param string $fullName.
     * @param mixed $value.
     * @return void
     */
    public function setVariableValue(string $fullName, mixed $value): void {
        // Split the full variable name into a prefix and the name itself
        list($framePrefix, $varName) = explode('@', $fullName, 2);

        // Use the setVariable method from MemoryModel
        $this->memoryModel->setVariable($framePrefix, $varName, $value);
    }
    
    /**
     * Creates a new temporary frame.
     * @return void
     */
    public function handleCREATEFRAME(): void {
        // Simply call the createTF method from MemoryModel to create a new TF
        $this->memoryModel->createTF();
    }

    /**
     * Moves the temporary frame to the local frame stack.
     * @return void
     */
    public function handlePUSHFRAME(): void {
        // Simply call the push method from MemoryModel to create a new TF
        $this->memoryModel->pushTFtoLF();
    }

    /**
     * Moves the top local frame from the stack to the temporary frame.
     * @return void
     */
    public function handlePOPFRAME(): void {
        // Simply call the pop method from MemoryModel to create a new TF
        $this->memoryModel->popLFtoTF();
    }
    
    /**
     * Defines a new variable.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleDEFVAR(array $args): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: DEFVAR expects exactly one argument. Error code: 32.\n");
            exit(32);
        }

        if ($args[0]['type'] !== 'var') {
            $this->stderr->writeString("Error: DEFVAR expects a variable as the first argument. Error code: 32.\n");
            exit(52);
        }
    
        $fullName = trim($args[0]['value']);
        $parts = explode('@', $fullName);
    
        if (count($parts) != 2) {
            $this->stderr->writeString("Error: Invalid variable format: $fullName. Error code: 52.\n");
            exit(52);
        }
    
        [$framePrefix, $varName] = $parts;
    
        $frame = $this->memoryModel->getFrame($framePrefix);
    
        if ($frame->variableExists($varName)) {
            $this->stderr->writeString("Error: Variable $varName already defined. Error code: 52.\n");
            exit(52);
        }
    
        $frame->defineVariable($varName, null);
    }
    
    /**
     * Performs a jump to a label while supporting return capability.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleCALL(array $args, int &$InstructionIndex): void {
        if (count($args) != 1) {
            $this->stderr->writeString("Error: CALL expects exactly one argument. Error code: 32.\n");
            exit(32);
        }
    
        $label = $args[0]['value'];
    
        // Using the getLabelPosition method, which returns the position of the label instruction
        $labelPosition = $this->labelManager->getLabelPosition($label);
    
        // Save the current position to the call stack for possible return
        array_push($this->callStack, $InstructionIndex);
    
        // Proceed to the instructions indicated by the label
        $InstructionIndex = $labelPosition;
    }

    /**
     * Returns to a position saved by the CALL instruction.
     * @param array<array{type: string, value: mixed}> $args
     * @return void
     */
    public function handleRETURN(array $args, int &$InstructionIndex): void {
        if (empty($this->callStack)) {
            // The call stack is empty, report an error
            $this->stderr->writeString("Error: Return attempted with an empty call stack. Error code: 56.\n");
            exit(56);
        }
        
        // Retrieve the last position from the call stack and set it as the current position
        $InstructionIndex = array_pop($this->callStack);
        $InstructionIndex++;
    }
}