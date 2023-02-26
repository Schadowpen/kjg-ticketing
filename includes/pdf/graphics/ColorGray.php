<?php

namespace KjG_Ticketing\pdf\graphics;

/**
 * Color in grayscale
 */
class ColorGray extends Color {
    /**
     * Grayscale in Range 0 to 1
     * @var float
     */
    protected float $gray;

    /**
     * @param float $gray Grayscale in Range 0 to 1
     */
    public function __construct( float $gray ) {
        $this->gray = $gray;
    }

    public function getGray(): float {
        return $this->gray;
    }
}