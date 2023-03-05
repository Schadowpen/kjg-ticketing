<?php


namespace KjG_Ticketing\pdf\graphics\operator;

/**
 * Operator zum Setzen des Farbraums und/oder Farbe für Linien zeichnen
 * @package KjG_Ticketing\pdf\graphics\operator
 */
class ColorStrokingOperator extends UnknownOperator {
    public function isGraphicsStateOperator(): bool {
        return true;
    }

    public function isRenderingOperator(): bool {
        return false;
    }
}