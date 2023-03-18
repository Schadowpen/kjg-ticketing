<?php

use KjG_Ticketing\database\DatabaseOverview;
use KjG_Ticketing\KjG_Ticketing_Security;
use KjG_Ticketing\Options;

function kjg_ticketing_admin_display(): void {
    $database_overview = new DatabaseOverview();

    // options managed on this site
    $selected_event_id = Options::get_current_event_id();
    $is_http_allowed   = Options::is_http_allowed();

    // additional data
    $current_event = false;
    $dbc           = $database_overview->getCurrentDatabaseConnection( false );
    if ( $dbc ) {
        $current_event = $dbc->get_event( false );
    }
    ?>
    <div class="wrap">
        <h1>This plugin is under construction</h1>

        <form action="<?php menu_page_url( 'kjg-ticketing-admin-display' ) ?>" method="post">
            <?php wp_nonce_field( 'kjg-ticketing-admin-display' ) ?>

            <!-- SELECTED EVENT ID -->

            <p class="error">
                <?php esc_html_e( "Before you change this setting", "kjg-ticketing" ); ?>:
                <?php esc_html_e( "Please ensure that nobody else is currently working on this event!", "kjg-ticketing" ); ?>
                <br/>
                <label>
                    <?php esc_html_e( "Select currently active event", "kjg-ticketing" ); ?>:
                    <select name="current_event_id">
                        <option value="0"
                            <?php echo ! $selected_event_id ? "selected" : "" ?>
                        >
                            <?php esc_html_e( "none", "kjg-ticketing" ); ?>
                        </option>
                        <?php
                        $events = $database_overview->getEvents();
                        foreach ( $events as $event ) {
                            ?>
                            <option value="<?php echo $event->id ?>"
                                <?php echo $event->id == $selected_event_id ? "selected" : "" ?>
                            >
                                <?php echo $event->name ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </label>
            </p>
            <?php
            if ( $current_event ) {
                ?>
                <br/>
                <?php esc_html_e( "Currently active event", "kjg-ticketing" ); ?>:
                <span style="font-style: italic"><?php echo $current_event->name ?></span>
                <?php
            }
            ?>

            <!-- IS HTTP ALLOWED -->

            <br/>
            <?php
            if ( $is_http_allowed ) {
                ?>
                <p class="error">
                    <?php esc_html_e( "Please verify that this website is served via an HTTPS connection!", "kjg-ticketing" ) ?>
                    <br/>
                    <?php esc_html_e( "This plugin is used to manage personal data.", "kjg-ticketing" ) ?>
                    <?php esc_html_e( "This data must be protected from access by third parties.", "kjg-ticketing" ) ?>
                    <?php esc_html_e( "The best way to do this is via an encrypted HTTPS connection.", "kjg-ticketing" ) ?>
                </p>
                <br/>
                <?php
            }
            ?>
            <label>
                <input name="http_allowed" type="checkbox" <?php echo $is_http_allowed ? 'checked="true"' : '' ?> />
                <?php esc_html_e( "Allow connections over HTTP", "kjg-ticketing" ); ?>
                (<?php esc_html_e( "insecure", "kjg-ticketing" ); ?>)
            </label>

            <!-- NONCE -->

            <br/>
            <label>
                WordPress Nonce (can be used for debugging)
                <input type="text" readonly value="<?php echo KjG_Ticketing_Security::create_AJAX_nonce() ?>">
            </label>

            <!-- SUBMIT -->

            <br/>
            <br/>
            <input type="submit" value="<?php esc_html_e( "Submit", "kjg-ticketing" ); ?>">
        </form>

    </div>
    <?php
}
