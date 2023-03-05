<?php


namespace KjG_Ticketing\pdf\document;

/**
 * Eine Multiple Master Font ist eine Schriftart, die konfiguriert werden kann hinsichtlich Fettheit oder Kursivität.
 * In PDF wird diese Konfigurierbarkeit nicht abgebildet, es wird ein Font1-Objekt erstellt, welches eine bestimmte Konfiguration der MultipleMasterFont darstellt
 * @package KjG_Ticketing\pdf\document
 */
class FontMultipleMaster extends SimpleFont {
    public static function objectSubtype(): ?string {
        return "MMType1";
    }
}