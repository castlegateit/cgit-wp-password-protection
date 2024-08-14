<?php

if (!isset($message) || !$message) {
    return;
}

?>

<div class="notice notice-warning">
    <p><?= esc_html($message) ?></p>
</div>
