<?php

namespace KjG_Ticketing\misc;
/**
 * The purpose of this class is to consecutively read a string.
 * The StringReader can be positioned at any position inside the string and then start reading per byte or per line.
 * <br/>
 * Therefore, use the functions starting with "read". Those update the current read position by the amount of bytes read.
 * To skip parts of the string without reading them, use functions that start with "skip".
 */
class StringReader {
    /**
     * String that is read by this StringReader.
     * Should be treated as readonly.
     */
    private string $string;
    /**
     * Length of $string, equal to strlen($this->string)
     */
    private int $stringLength;
    /**
     * Current (Byte-) position of the reader
     * @var int
     */
    private int $readerPos;

    /**
     * Creates a new StringReader
     *
     * @param string $string which should be read
     * @param int $readerPos At which byte position the reader should start reading. If not set, the StringReader starts at the start of the string.
     */
    public function __construct( string $string, int $readerPos = 0 ) {
        $this->string = $string;
        $this->stringLength = strlen( $string );
        $this->readerPos = $readerPos;
    }

    /**
     * Returns the string which is read by the StringReader
     * @see StringReader::getStringLength()
     */
    public function getString(): string {
        return $this->string;
    }

    /**
     * Returns the length of the string
     * @see StringReader::getString()
     */
    public function getStringLength(): int {
        return $this->stringLength;
    }

    /**
     * Returns the current reader position
     */
    public function getReaderPos(): int {
        return $this->readerPos;
    }

    /**
     * Sets a new reader position
     *
     * @param int $readerPos
     */
    public function setReaderPos( int $readerPos ): void {
        $this->readerPos = $readerPos;
    }


    /**
     * Returns the Byte at the given position.
     * If the position is outside of the string, null is returned.
     *
     * @param int $pos
     *
     * @return string|null
     */
    public function getByte( int $pos ): string|null {
        return @$this->string[ $pos ];
    }

    /**
     * Returns a substring of the read string
     *
     * @param int $start Start of the substring
     * @param int $length Länge of the substring
     *
     * @see substr()
     */
    public function getSubstring( int $start, int $length ): string|false {
        return substr( $this->string, $start, $length );
    }


    /**
     * Reads a single Byte / Char
     * @return string
     * @throws \Exception When the string has reached the End
     */
    public function readByte(): string {
        if ( $this->isAtEndOfString() ) {
            throw new \Exception( "unable to read Byte, StringReader already at End of String" );
        }

        $byte = $this->string[ $this->readerPos ];
        ++ $this->readerPos;

        return $byte;
    }

    /**
     * Reads a substring with the specified length
     *
     * @param int $length
     *
     * @return string
     * @throws \Exception When the string is not long enough to read the substring
     */
    public function readSubstring( int $length ): string {
        if ( $this->exceedsEndOfString( $length ) ) {
            throw new \Exception( "unable to read Bytes, StringReader would exceed End of String" );
        }

        $str = substr( $this->string, $this->readerPos, $length );
        $this->readerPos += $length;

        return $str;
    }

    /**
     * Reads until the end of the current line. The line break is not included in the return.
     * The reader position is set at the start of the next line.
     * @return string
     */
    public function readLine(): string {
        // find next line break
        $lineLength = strcspn( $this->string, "\r\n", $this->readerPos );
        $lineEnd = $this->readerPos + $lineLength;
        $lineContent = substr( $this->string, $this->readerPos, $lineLength );

        $this->skipLineBreakChars( $lineEnd );

        return $lineContent;
    }

    /**
     * Reads a string, but only as long as the characters defined in $mask occur.
     * As soon as another character occurs, reading stops.
     *
     * @param string $mask Mask with all characters that are allowed to be read.
     *
     * @return string
     * @see strspn()
     */
    public function readOnlyMask( string $mask ): string {
        $length = strspn( $this->string, $mask, $this->readerPos );
        $str = substr( $this->string, $this->readerPos, $length );
        $this->readerPos += $length;

        return $str;
    }

    /**
     * Reads a string, but only as long as the characters defined in $mask occur.
     * As soon as another character occurs or $maxLength characters were read, reading stops.
     *
     * @param string $mask Mask with all characters that are allowed to be read.
     * @param int $maxLength Maximum number of Bytes to be read.
     *
     * @return string
     * @see strspn()
     */
    public function readOnlyMaskWithMaxLength( string $mask, int $maxLength ) {
        $length = strspn( $this->string, $mask, $this->readerPos, $maxLength );
        $str = substr( $this->string, $this->readerPos, $length );
        $this->readerPos += $length;

        return $str;
    }

    /**
     * Reads a string until one of the characters defined in $mask occurs.
     *
     * @param string $mask Mask with all characters that trigger reading to stop.
     *
     * @return string
     * @see strcspn()
     */
    public function readUntilMask( string $mask ): string {
        $length = strcspn( $this->string, $mask, $this->readerPos );
        $str = substr( $this->string, $this->readerPos, $length );
        $this->readerPos += $length;

        return $str;
    }


    /**
     * Skips a single Byte/Character
     */
    public function skipByte() {
        ++ $this->readerPos;
    }

    /**
     * Skips a substring with the given length.
     * If a negative $length is given, the latest Bytes are restored for reading again.
     *
     * @param int $length
     */
    public function skipSubstring( int $length ) {
        $this->readerPos += $length;
    }

    /**
     * Skips the current line
     */
    public function skipLine() {
        // find next line break
        $lineLength = strcspn( $this->string, "\r\n", $this->readerPos );
        $lineEnd = $this->readerPos + $lineLength;

        $this->skipLineBreakChars( $lineEnd );
    }

    /**
     * Skips the string for all characters defined in $mask.
     *
     * @param string $mask Mask containing all characters that should be skipped
     *
     * @see strspn()
     */
    public function skipOnlyMask( string $mask ) {
        $length = strspn( $this->string, $mask, $this->readerPos );
        $this->readerPos += $length;
    }

    /**
     * Skips the string until one of the characters defined in $mask occurs.
     *
     * @param string $mask Mask containing all characters that should stop skipping
     *
     * @see strcspn()
     */
    public function skipUntilMask( string $mask ) {
        $length = strcspn( $this->string, $mask, $this->readerPos );
        $this->readerPos += $length;
    }

    /**
     * Makes the last Byte unread by decreasing the current reader position by 1.
     */
    public function retrieveLastByte() {
        -- $this->readerPos;
    }


    /**
     * Returns if the StringReader has reached the end of the string.
     * @return bool true if the end was reached
     */
    public function isAtEndOfString(): bool {
        return $this->readerPos >= $this->stringLength;
    }

    /**
     * Returns if reading the given number of Bytes would overrun the string length.
     *
     * @param int $bytesToRead Number of bytes to read, starting at the current reader position
     *
     * @return bool true when the string is threatened to be overrun
     */
    public function exceedsEndOfString( int $bytesToRead ): bool {
        return $this->readerPos + $bytesToRead > $this->stringLength;
    }

    /**
     * Checks which type of line break can be found at the given position and sets the reader position to the next line, if possible.
     *
     * @param int $lineEnd
     *
     * @return void
     */
    private function skipLineBreakChars( int $lineEnd ): void {
        if ( $lineEnd === $this->stringLength ) {
            $this->readerPos = $lineEnd;
        } else if ( $this->string[ $lineEnd ] === "\r" && @$this->string[ $lineEnd + 1 ] === "\n" ) {
            $this->readerPos = $lineEnd + 2;
        } else {
            $this->readerPos = $lineEnd + 1;
        }
    }
}