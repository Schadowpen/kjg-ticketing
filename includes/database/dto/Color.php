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
     * Constructs a Color with RGB values in range 0 to 255
     * Values down to -128 are wrapped around to support signed 8-bit integers.
     *
     * It is not checked if a value is outside this range
     */
    public function __construct( int $red, int $green, int $blue ) {
        $this->red   = $this->from_maybe_negative( $red );
        $this->green = $this->from_maybe_negative( $green );
        $this->blue  = $this->from_maybe_negative( $blue );
    }

    private function from_maybe_negative( int $color_value ): int {
        if ( $color_value < 0 && $color_value >= - 128 ) {
            $color_value += 256;
        }

        return $color_value;
    }

    public function to_PDF_color(): ColorRGB {
        return new ColorRGB(
            ( (float) $this->red ) / 255.0,
            ( (float) $this->green ) / 255.0,
            ( (float) $this->blue ) / 255.0
        );
    }
}