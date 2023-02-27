<?php

namespace KjG_Ticketing\database\dto;

class Show {
    public int $id;
    public string $date;
    public string $time;

    public static function from_DB( \stdClass $db_row ): Show {
        $show = new Show();
        $show->id = intval( $db_row->id );
        $show->date = (string) $db_row->show_date;
        $show->time = (string) $db_row->show_time;

        return $show;
    }
}