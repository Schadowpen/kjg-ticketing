<?php

namespace KjG_Ticketing\database\dto;

class ProcessAdditionalField {
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