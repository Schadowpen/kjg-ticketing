<?php

namespace KjG_Ticketing\database;

/**
 * Class for mapping an old unique ID to a new unique ID.
 * This is especially useful when copying an event and all its dependent database entries with their own unique IDs.
 */
class IdMapping {
    private int $old_ID;
    private int $new_ID;

    function __construct( int $old_ID, int $new_ID ) {
        $this->old_ID = $old_ID;
        $this->new_ID = $new_ID;
    }

    public function get_old_ID(): int {
        return $this->old_ID;
    }

    public function get_new_ID(): int {
        return $this->new_ID;
    }

    /**
     * @param IdMapping[] $ID_mapping
     * @param int $old_ID The ID you want to find in the ID_mapping array
     *
     * @return bool|int The new ID, or false if it does not occur in ID_mapping
     */
    public static function find_new_ID( array $ID_mapping, int $old_ID ): bool|int {
        foreach ( $ID_mapping as $single_ID_mapping ) {
            if ( $single_ID_mapping->old_ID === $old_ID ) {
                return $single_ID_mapping->new_ID;
            }
        }

        return false;
    }
}