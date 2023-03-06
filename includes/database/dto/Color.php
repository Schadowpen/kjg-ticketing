<?php

namespace KjG_Ticketing\database\dto;

use KjG_Ticketing\pdf\graphics\ColorRGB;

/**
 * Color representation in red, green and blue.
 * Values are in range 0 to 255
 */
class Color {
    /**
     * @var int Value of red in range 0 to 255
     */
    public int $red;
    /**
     * @var int Value of green in range 0 to 255
     */
    public int $green;
    /**
     * @var int Value of blue in range 0 to 255
     */
    public int $blue;

    /**
     * @param int $red Value of red in range 0 to 255
     * @param int $green Value of green in range 0 to 255
     * @param int $blue Value of blue in range 0 to 255
     */
    public function __construct( int $red, int $green, int $blue ) {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public function to_PDF_color(): ColorRGB {
        return new ColorRGB(
            ( (float) $this->red ) / 255.0,
            ( (float) $this->green ) / 255.0,
            ( (float) $this->blue ) / 255.0
        );
    }
}