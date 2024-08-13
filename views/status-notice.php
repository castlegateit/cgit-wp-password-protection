<?php

if (!isset($message) || !$message) {
    return;
}

?>

<div class="notice notice-warning">
    <p><?= $message ?></p>
</div>
