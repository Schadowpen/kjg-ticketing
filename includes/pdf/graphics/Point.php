<?php

namespace KjG_Ticketing\pdf\graphics;

/**
 * A point in a 2-dimensional coordinate system
 */
class Point {
    /**
     * X value of the point in the coordinate system
     */
    public float $x;
    /**
     * Y value of the point in the coordinate system
     */
    public float $y;

    public function __construct( float $x, float $y ) {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Calculates the distance between this point and the given point
     *
     * @param Point $p
     *
     * @return float
     */
    public function distanceTo( Point $p ): float {
        $dx = $this->x - $p->x;
        $dy = $this->y - $p->y;

        return sqrt( $dx * $dx + $dy * $dy );
    }
}