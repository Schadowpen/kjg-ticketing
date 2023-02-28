<?php

namespace KjG_Ticketing\database\dto;

class ProcessAdditionalField {
    // possible values for data_type enum
    public const DATA_TYPE_INT = "integer";
    public const DATA_TYPE_FLOAT = "float";
    public const DATA_TYPE_STRING = "string";
    public const DATA_TYPE_LONG_STRING = "longString";
    public const DATA_TYPE_BOOLEAN = "boolean";

    public int $id;
    public string $description;
    public string $data_type;
    public bool $required;

    public static function from_DB( \stdClass $db_row ): ProcessAdditionalField {
        $additional_field = new ProcessAdditionalField();
        $additional_field->id = intval( $db_row->id );
        $additional_field->description = (string) $db_row->description;
        $additional_field->data_type = (string) $db_row->data_type;
        $additional_field->required = intval( $db_row->required ) === 1;

        return $additional_field;
    }
}