<?php


namespace KjG_Ticketing\pdf\document;

/**
 * Eine Schriftart, deren Glyphen durch Content Streams anstelle eines eingebetteten Font Programms beschrieben werden.
 * @package KjG_Ticketing\pdf\document
 */
class FontType3 extends SimpleFont {
    public static function objectSubtype(): ?string {
        return "Type3";
    }
}