<?php


namespace KjG_Ticketing\pdf\graphics\operator;


/**
 * Operator, der den aktuellen Pfad füllt. Welche Flächen zu füllen sind und welche nicht, wird nach der Even-Odd-Regel bestimmt.
 * Offene Unterpfade, also welche die einen Anfang und ein Ende haben, werden vorher geschlossen
 * @package KjG_Ticketing\pdf\graphics\operator
 */
class FillPathEvenOddOperator extends PathPaintingOperator {

    /**
     * Liefert den Operatoren, wie er im ContentStream vorkommt
     * @return string
     */
    function getOperator(): string {
        return "f*";
    }

    /**
     * Parst den Operatoren zu einem String, wie er in einem ContentStream vorkommt.
     * Dieser Beinhaltet auch einen EOL-Marker am Ende des Operatoren
     * @return string
     */
    function __toString(): string {
        return "f*\n";
    }
}