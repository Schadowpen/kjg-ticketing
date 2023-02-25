<?php

function kjg_ticketing_admin_display(): void {
    $database_overview = new KjG_Ticketing\database\DatabaseOverview();

    $selected_event_id = \KjG_Ticketing\Options::get_current_event_id();

    $current_event = false;
    $dbc = $database_overview->getCurrentDatabaseConnection( false );
    if ( $dbc ) {
        $current_event = $dbc->get_event( false );
    }
    ?>
    <div class="wrap">
        <h1>This plugin is under construction</h1>

        <form action="<?php menu_page_url( 'kjg-ticketing-admin-display' ) ?>" method="post">
            <?php wp_nonce_field( 'kjg-ticketing-admin-display' ) ?>

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
                <label>
                    <?php esc_html_e( "Currently active event", "kjg-ticketing" ); ?>:
                    <span style="font-style: italic"><?php echo $current_event->name ?></span>
                </label>
                <?php
            }
            ?>
            <br/>
            <br/>
            <input type="submit" value="<?php esc_html_e( "Submit", "kjg-ticketing" ); ?>">
        </form>

    </div>
    <?php
}
