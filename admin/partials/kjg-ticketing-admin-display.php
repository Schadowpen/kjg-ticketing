<?php

function kjg_ticketing_admin_display() {
    ?>
    <h1>This plugin is under construction</h1>

    <form action="<?php menu_page_url('kjg-ticketing-admin-display') ?>" method="post">
        <?php wp_nonce_field('kjg-ticketing-admin-display') ?>
        <label>
            Test Setting input:
            <input type="text"/>
        </label>
        <input type="submit" value="Submit">
    </form>

    <?php
}
