<?php

namespace KjG_Ticketing\database\dto;

use stdClass;

class Process {
    // possible values for payment_method enum
    public const PAYMENT_METHOD_CASH = "cash";
    public const PAYMENT_METHOD_BANK = "bank";
    public const PAYMENT_METHOD_PAYPAL = "PayPal";
    public const PAYMENT_METHOD_BOX_OFFICE = "box_office";
    public const PAYMENT_METHOD_VIP = "VIP";
    public const PAYMENT_METHOD_TRIPLE_A = "TripleA";

    // possible values for payment_state enum
    public const PAYMENT_STATE_OPEN = "open";
    public const PAYMENT_STATE_PAID = "paid";
    public const PAYMENT_STATE_BOX_OFFICE = "box_office";

    // possible values for shipping enum
    public const SHIPPING_PICK_UP = "pick_up";
    public const SHIPPING_MAIL = "mail";
    public const SHIPPING_EMAIL = "email";

    public int $id;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $address;
    public ?string $phone;
    public ?string $email;
    public ?float $ticket_price;
    public string $payment_method;
    public string $payment_state;
    public string $shipping;
    public ?string $comment;
    public bool $ticket_generated;
    /**
     * @var ProcessAdditionalEntry[]
     *
     * TODO is this field optional or shall we make it required?
     */
    public ?array $additional_entries;

    /**
     * @param stdClass $db_row
     * @param array|null $additional_entries The given array will be filtered for entries with the correct process_id,
     * no need to filter it beforehand.
     *
     * @return Process
     */
    public static function from_DB( stdClass $db_row, array|null $additional_entries = null ): Process {
        $process = new Process();
        $process->id = $db_row->id;
        $process->first_name = $db_row->first_name != null ? (string) $db_row->first_name : null;
        $process->last_name = $db_row->last_name != null ? (string) $db_row->last_name : null;
        $process->address = $db_row->address != null ? (string) $db_row->address : null;
        $process->phone = $db_row->phone != null ? (string) $db_row->phone : null;
        $process->email = $db_row->email != null ? (string) $db_row->email : null;
        $process->ticket_price = $db_row->ticket_price != null ? floatval( $db_row->ticket_price ) : null;
        $process->payment_method = (string) $db_row->payment_method;
        $process->payment_state = (string) $db_row->payment_state;
        $process->shipping = (string) $db_row->shipping;
        $process->comment = $db_row->comment != null ? (string) $db_row->comment : null;
        $process->ticket_generated = intval( $db_row->ticket_generated ) === 1;

        if ( $additional_entries != null ) {
            $process->additional_entries = array_filter(
                $additional_entries,
                function ( ProcessAdditionalEntry $entry ) use ( $process ) {
                    return $entry->process_id === $process->id;
                }
            );
        }

        return $process;
    }

    public function get_ticket_price( Event $event ): float {
        if ( $this->ticket_price != null ) {
            return $this->ticket_price;
        } else {
            return $event->ticket_price;
        }
    }
}