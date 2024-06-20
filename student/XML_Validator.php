<?php

namespace IPP\Student;

use DOMXPath;
use DOMElement;
use IPP\Core\Interface\OutputWriter;

/**
 * Class for validating XML structure.
 * @author Slabik Yaroslav (xslabi01)
 */
class XML_Validator 
{
    private OutputWriter $stderr;

    public function __construct(OutputWriter $stderr) {
        $this->stderr = $stderr;
    }

    /**
     * Validates XML structure and checks for duplicate instructions.
     * @param DOMXPath $xpath XPath processor instance
     * @param DOMElement[] $sortedInstructions Array of DOMElements representing instructions
     * @return void
     */
    public function validateXML(DOMXPath $xpath, array $sortedInstructions): void {
        $seenOrders = [];

        $argOutsideInstruction = $xpath->query('/program/arg1 | /program/arg2');
        if ($argOutsideInstruction->length > 0) {
            $this->stderr->writeString("Error: Arguments found outside of instruction elements. Error code: 32.\n");
            exit(32);
        }

        $programElements = $xpath->query('/program/*');
        foreach ($programElements as $element) {
            if ($element->nodeName !== 'instruction') {
                $this->stderr->writeString("Error: Unexpected element '{$element->nodeName}' found inside 'program'. Error code: 32.\n");
                exit(32);
            }
        }

        foreach ($sortedInstructions as $instruction) {
            $order = (int)$instruction->getAttribute('order');

            if ($order < 1) {
                $this->stderr->writeString("Error: Instruction order must be at least 1. Error code: 32.\n");
                exit(32);
            }

            if (in_array($order, $seenOrders)) {
                $this->stderr->writeString("Error: Duplicate order value detected. Error code: 32.\n");
                exit(32);
            }
            $seenOrders[] = $order;
        }
    }
}
