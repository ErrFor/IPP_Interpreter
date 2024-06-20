<?php

namespace IPP\Student;

use IPP\Core\Interface\OutputWriter;

/**
 * Class for managing memory model
 * @author Slabik Yaroslav (xslabi01)
 */
class MemoryModel extends Frame
{
    protected Frame $GF; // Global frame
    /** @var Frame[] */
    protected array $LFStack = []; // Local frame stack
    protected ?Frame $TF = null; // Temporary frame
    protected OutputWriter $stderr;
    
    public function __construct(OutputWriter $stderr) {
        $this->GF = new Frame();
        $this->stderr = $stderr;
    }

    /**
     * Creates a new temporary frame
     * @return void
     */
    public function createTF(): void {
        if ($this->TF !== null) {
            $this->TF->clearVariables();
        }
        $this->TF = new Frame();
    }

    /**
     * Pushes the temporary frame to the local frame stack
     * @return void
     */
    public function pushTFtoLF(): void {
        if ($this->TF === null) {
            $this->stderr->writeString("Error: TF is not defined. Error code: 55\n");
            exit(55);
        }
        array_push($this->LFStack, $this->TF);
        $this->TF = null;
    }

    /**
     * Pops the last local frame from the stack to the temporary frame
     * @return void
     */
    public function popLFtoTF(): void {
        if (empty($this->LFStack)) {
            $this->stderr->writeString("Error: No LF available to pop to TF. Error code: 55\n");
            exit(55);
        }
        $this->TF = array_pop($this->LFStack);
    }

    /**
     * Gets the global frame
     * @return Frame
     */
    public function getGF(): Frame {
        return $this->GF;
    }

    /**
     * Gets the temporary frame
     * @return Frame|null Returns the TF or null if TF not defined
     */
    public function getTF(): ?Frame {
        return $this->TF;
    }

    /**
     * Gets the local frame stack
     * @return Frame[] Returns the LFStack
     */
    public function getLFStack(): array {
        return $this->LFStack;
    }

    /**
     * Sets the variable in the given frame
     * @param string $framePrefix The prefix of the frame
     * @param string $name The name of the variable
     * @param mixed $value The value to set
     * @return void
     */
    public function setVariable(string $framePrefix, string $name, mixed $value): void {
        $frame = $this->getFrame($framePrefix);
        if (!$frame->variableExists($name)) {
            $this->stderr->writeString("Error: Variable $name not defined in frame $framePrefix. Error code: 54.\n");
            exit(54);
        }
        $frame->defineVariable($name, $value);
    }
    

    /**
     * Gets the value of the variable in the given frame
     * @param string $framePrefix The prefix of the frame
     * @param string $name The name of the variable
     * @return mixed The value of the variable
     */
    public function getVariable(string $framePrefix, string $name): mixed {
        $frame = $this->getFrame($framePrefix);
        $value = $frame->getVariableInFrame($name);
        return $value;
    }

    /**
     * Gets the frame by its prefix
     * @param string $framePrefix The prefix of the frame
     * @return Frame The frame
     */
    public function getFrame(string $framePrefix): Frame {
        switch ($framePrefix) {
            case 'GF':
                return $this->GF;
            case 'LF':
                return end($this->LFStack);
            case 'TF':
                if ($this->TF === null) {
                    $this->stderr->writeString("Error: TF is not defined. Error code: 54.\n");
                    exit(55);
                }
                return $this->TF; 
            default:
                $this->stderr->writeString("Error: Invalid frame prefix: $framePrefix. Error code: 32.\n");
                exit(32);
        }
    }
    
}
