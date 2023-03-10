<?php


namespace KjG_Ticketing\pdf\graphics\operator;

/**
 * Operator zum hinzufügen des aktuellen Path zum Clipping Path. Der Path wird nach der Even-Odd-Methode verarbeitet.
 * Dieser Operator sollte direkt vor einem Path Painting Operator aufgerufen werden.
 * @package KjG_Ticketing\pdf\graphics\operator
 */
class ClippingPathEvenOddOperator extends AbstractOperator {

    /**
     * Liefert den Operatoren, wie er im ContentStream vorkommt
     * @return string
     */
    function getOperator(): string {
        return "W*";
    }

    /**
     * Parst den Operatoren zu einem String, wie er in einem ContentStream vorkommt
     * @return string
     */
    function __toString(): string {
        return "W*\n";
    }

    public function isGraphicsStateOperator(): bool {
        return true;
    }
}