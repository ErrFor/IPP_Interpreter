<?php

namespace IPP\Student;

use DOMElement;
use IPP\Core\Interface\OutputWriter;

/**
 * Class for managing labels in the XML representation of the program.
 * @author Slabik Yaroslav (xslabi01)
 * @property array<string, int> $labels
 */
class LabelManager 
{
    /** @var array<string, int> Labels and their positions */
    protected array $labels = [];

    protected OutputWriter $stderr;

    public function __construct(OutputWriter $stderr) {
        $this->stderr = $stderr;
    }

    /**
     * Initializes labels with their instruction positions.
     * @param DOMElement[] $sortedInstructions Array of instruction elements.
     * @return void
     */
    public function initializeLabels(array $sortedInstructions): void {
        foreach ($sortedInstructions as $instructionElement) {
            $opcode = $instructionElement->getAttribute('opcode');
            if ($opcode === 'LABEL') {
                $labelName = trim($instructionElement->getElementsByTagName('arg1')[0]->nodeValue);
                $labelOrder = (int) $instructionElement->getAttribute('order');
                $this->defineLabel($labelName, $labelOrder);
            }
        }
    }

    /**
     * Defines a label with a given name and instruction position.
     * @param string $labelName Name of the label.
     * @param int $instructionPosition Position of the instruction.
     * @return void
     */
    public function defineLabel(string $labelName, int $instructionPosition): void {
        if (isset($this->labels[$labelName])) {
            $this->stderr->writeString("Error: Label $labelName already defined. Error code: 52.\n");
            exit(52);
        }
        $this->labels[$labelName] = $instructionPosition;
    }

    /**
     * Returns the position of a label.
     * @param string $labelName Name of the label.
     * @return int Position of the label.
     */
    public function getLabelPosition(string $labelName): int {
        if (!isset($this->labels[$labelName])) {
            $this->stderr->writeString("Error: Label $labelName not found. Error code: 52.\n");
            exit(52);
        }
        return $this->labels[$labelName];
    }
}
