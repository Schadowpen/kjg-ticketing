<?php


namespace KjG_Ticketing\pdf\graphics\operator;

/**
 * Operator zum Identifizieren von Marked Content in einem Content Stream.
 * Da dies für den Anwendungszweck nicht relevant ist, werden alle Operatoren mit dieser Oberklasse zusammengefasst.
 * @package KjG_Ticketing\pdf\graphics\operator
 */
class MarkedContentOperator extends UnknownOperator {
    public function isRenderingOperator(): bool {
        return false;
    }
}