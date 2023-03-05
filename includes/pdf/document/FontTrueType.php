<?php


namespace KjG_Ticketing\pdf\document;

/**
 * Eine Schriftart von TrueType.
 * @package KjG_Ticketing\pdf\document
 */
class FontTrueType extends SimpleFont {
    public static function objectSubtype(): ?string {
        return "TrueType";
    }
}