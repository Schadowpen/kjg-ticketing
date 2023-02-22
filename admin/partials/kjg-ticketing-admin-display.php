<?php

function kjg_ticketing_admin_display(): void {
    ?>
    <h1>This plugin is under construction</h1>

    <form action="<?php menu_page_url('kjg-ticketing-admin-display') ?>" method="post">
        <?php wp_nonce_field('kjg-ticketing-admin-display') ?>
        <label>
            Test Setting input:
            <input type="text" name="testText"/>
        </label>
        <input type="submit" value="Submit">
    </form>
    <br/>

    <?php esc_html_e('Seating plan', 'kjg-ticketing'); ?>

    <?php
}
