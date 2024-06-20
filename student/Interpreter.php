<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;

/**
 * Main class for interpreting IPPcode24 XML representation
 * @author Slabik Yaroslav (xslabi01)
 */
class Interpreter extends AbstractInterpreter
{
    /**
     * Main method for interpreting the XML representation of the program.
     * @return int The exit code of the program.
     */
    public function execute(): int
    {   
        $dom = $this->source->getDOMDocument();
        $xpath = new \DOMXPath($dom);

        // Initialization of helper classes for instruction classes
        $memoryModel = new MemoryModel($this->stderr); // Initialization of MemoryModel class
        $labelManager = new LabelManager($this->stderr); // Initialization of LabelManager class
        $xmlValidator = new XML_Validator($this->stderr); // Initialization of XML_Validator class
        $ArgControl = new ArgControl($this->stderr); // Initialization of ArgControl class
        $ValueHandling = new ValueHandling($memoryModel, $this->stderr); // Initialization of ValueHandling class
        
        //Initialization of classes for instruction processing
        $MemoryFrames = new MemoryFrames($memoryModel, $labelManager, $this->stderr); // Initialization of MemoryFrames  class
        $DataStack = new DataStack($ValueHandling, $this->stderr); // Initialization of DataStack class
        $ExprCalculation = new ExprCalculation($ValueHandling, $this->stderr); // Initialization of ExprCalculation class
        $InputOutput = new InputOutput($ValueHandling, $this->stderr, $this->stdout, $this->input); // Initialization of InputOutput class
        $String_Instructions = new Strings($ValueHandling, $this->stderr); // Initialization of String_Instructions class
        $Types_Instruction = new Types($ValueHandling, $this->stderr); // Initialization of Types_Instruction class
        $ProgramFlow = new ProgramFlow($ValueHandling, $labelManager, $this->stderr); // Initialization of ProgramFlow class
        $Debugging = new Debugging($memoryModel, $ValueHandling, $this->stderr); // Initialization of Debugging class

        // Receive and sort all instructions by order attribute
        $instructions = $xpath->query('/program/instruction');
        $sortedInstructions = iterator_to_array($instructions);
        $InstructionIndex = 0;
        $InstructionCount = 0;
        usort($sortedInstructions, function ($a, $b) {
            return (int)$a->getAttribute('order') <=> (int)$b->getAttribute('order');
        });

        $xmlValidator->validateXML($xpath, $sortedInstructions);

        $labelManager->initializeLabels($sortedInstructions);

        // Processing of each instruction
        while ($InstructionIndex < count($sortedInstructions)) {
            $instruction = $sortedInstructions[$InstructionIndex];
            $opcode = strtoupper($instruction->getAttribute('opcode'));
            $args = $ArgControl->getArgs($instruction);

            // Selecting and executing an opcode-dependent operation
            switch ($opcode) {
                case 'MOVE':
                case 'CREATEFRAME':
                case 'PUSHFRAME':
                case 'POPFRAME':
                case 'DEFVAR':
                    $methodName = "handle" . $opcode;
                    $MemoryFrames->$methodName($args);
                    break;
                case 'CALL':
                case 'RETURN':        
                    $methodName = "handle" . $opcode;
                    $MemoryFrames->$methodName($args, $InstructionIndex);
                    continue 2;
                case 'PUSHS':
                case 'POPS':
                    $methodName = "handle" . $opcode;
                    $DataStack->$methodName($args);
                    break;
                case 'ADD':
                case 'SUB':
                case 'MUL':
                case 'IDIV':
                case 'NOT':
                case 'INT2CHAR':
                case 'STRI2INT':
                    $methodName = "handle" . $opcode;
                    $ExprCalculation->$methodName($args);
                    break;
                case 'LT':
                case 'GT':
                case 'EQ':
                    $ExprCalculation->handleComparison($args, $opcode);
                    break;
                case 'AND':
                case 'OR':
                    $ExprCalculation->handleBooleanOperation($args, $opcode);
                    break;
                case 'READ':
                case 'WRITE':
                    $methodName = "handle" . $opcode;
                    $InputOutput->$methodName($args);
                    break;
                case 'CONCAT':
                case 'STRLEN':
                case 'GETCHAR':
                case 'SETCHAR':
                    $methodName = "handle" . $opcode;
                    $String_Instructions->$methodName($args);
                    break;
                case 'TYPE':
                    $methodName = "handle" . $opcode;
                    $Types_Instruction->$methodName($args);
                    break;
                case 'LABEL':
                case 'EXIT':   
                    $methodName = "handle" . $opcode;
                    $ProgramFlow->$methodName($args);
                    break; 
                case 'JUMP':
                case 'JUMPIFEQ':
                case 'JUMPIFNEQ':
                    $methodName = "handle" . $opcode;
                    $ProgramFlow->$methodName($args, $InstructionIndex);
                    continue 2;
                case 'DPRINT':
                    $Debugging->handleDPRINT($args);
                    break;
                case 'BREAK':
                    $Debugging->handleBREAK($InstructionIndex, $InstructionCount);
                    break;                    
                default:
                    $this->stderr->writeString("Error: Unknown opcode '{$opcode}' found. Error code: 32.\n");
                    exit(32);
            }
            $InstructionIndex++;
            $InstructionCount++;
        }

        return 0; // Return 0 on successful execution
    }
} 
