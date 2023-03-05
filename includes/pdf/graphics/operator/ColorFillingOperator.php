<?php


namespace KjG_Ticketing\pdf\graphics\operator;

/**
 * Operator zum Setzen des Farbraums und/oder Farbe für Flächen zeichnen
 * @package KjG_Ticketing\pdf\graphics\operator
 */
class ColorFillingOperator extends UnknownOperator {
    public function isGraphicsStateOperator(): bool {
        return true;
    }

    public function isRenderingOperator(): bool {
        return false;
    }
}