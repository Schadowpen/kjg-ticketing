<?php

namespace KjG_Ticketing\pdf\graphics;

/**
 * Color in red, green and blue
 */
class ColorRGB extends Color {
    /**
     * Value of red in range 0 to 1
     * @var float
     */
    protected float $red;
    /**
     * Value of green in range 0 to 1
     * @var float
     */
    protected float $green;
    /**
     * Value of blue in range 0 to 1
     * @var float
     */
    protected float $blue;

    /**
     * @param float $red Value of red in range 0 to 1
     * @param float $green Value of green in range 0 to 1
     * @param float $blue Value of blue in range 0 to 1
     */
    public function __construct( float $red, float $green, float $blue ) {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    /**
     * Creates a Color from a hexadecimal representation in format #rrggbb
     *
     * @param string $hex Hexadezimal String from #000000 to #ffffff
     *
     * @return ColorRGB
     */
    public static function fromHex( string $hex ): ColorRGB {
        $dec = hexdec( substr( $hex, 1 ) );
        $r = ( $dec >> 16 ) & 0xFF;
        $g = ( $dec >> 8 ) & 0xFF;
        $b = $dec & 0xFF;

        return new ColorRGB( $r / 255.0, $g / 255.0, $b / 255.0 );
    }

    public function getRed(): float {
        return $this->red;
    }

    public function getGreen(): float {
        return $this->green;
    }

    public function getBlue(): float {
        return $this->blue;
    }
}