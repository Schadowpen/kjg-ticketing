<?php

namespace KjG_Ticketing\pdf\misc;

/**
 * A range from start (inclusive) until end (exclusive),
 * dedicated for working with array positions.
 */
class Range {
    /**
     * Start index of range (inclusive)
     */
    protected int $startIndex;
    /**
     * End index of range (exclusive)
     */
    protected int $endIndex;

    /**
     * @param int $startIndex Start index of range (inclusive)
     * @param int $endIndex End index of range (exclusive)
     */
    public function __construct( int $startIndex, int $endIndex ) {
        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;
    }

    public function getStartIndex(): int {
        return $this->startIndex;
    }

    public function setStartIndex( int $startIndex ): void {
        $this->startIndex = $startIndex;
    }

    /**
     * Decreases the start index by 1
     */
    public function decreaseStartIndex(): void {
        -- $this->startIndex;
    }

    public function getEndIndex(): int {
        return $this->endIndex;
    }

    public function setEndIndex( int $endIndex ): void {
        $this->endIndex = $endIndex;
    }

    /**
     * Increases the end index by 1
     */
    public function increaseEndIndex(): void {
        ++ $this->endIndex;
    }

    public function getLength(): int {
        return $this->endIndex - $this->startIndex;
    }

    /**
     * The start index stays the same, just the end index is calculated new.
     *
     * @param int $length New length of the range
     */
    public function setLength( int $length ): void {
        $this->endIndex = $this->startIndex + $length;
    }
}