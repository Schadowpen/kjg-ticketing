<?php

namespace KjG_Ticketing\database\dto;

class ProcessAdditionalEntry {
    public int $process_id;
    public int $field_id;
    protected bool|int|float|string $value;
    /**
     * @var string|null Copy from the according ProcessAdditionalField. Defines the data type of $value.
     */
    protected ?string $data_type;

    private function __construct() {
        // use static functions instead of constructor
    }

    /**
     * @return bool|float|int|string
     */
    public function get_value(): float|bool|int|string {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function get_data_type(): ?string {
        return $this->data_type;
    }

    public static function from_DB( \stdClass $db_row, string $data_type ): ProcessAdditionalEntry {
        $entry             = new ProcessAdditionalEntry();
        $entry->process_id = intval( $db_row->process_id );
        $entry->field_id   = intval( $db_row->field_id );
        $entry->value      = match ( $data_type ) {
            ProcessAdditionalField::DATA_TYPE_BOOLEAN => intval( $db_row->boolean_value ) === 1,
            ProcessAdditionalField::DATA_TYPE_INT => intval( $db_row->integer_value ),
            ProcessAdditionalField::DATA_TYPE_FLOAT => floatval( $db_row->float_value ),
            default => (string) $db_row->string_value,
        };
        $entry->data_type  = $data_type;

        return $entry;
    }
}
