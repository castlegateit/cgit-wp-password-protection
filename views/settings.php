<?php

if (!isset($nonce) || !isset($mode) || !isset($password)) {
    return;
}

?>

<div class="wrap">
    <h1><?= esc_html__('Password Protection') ?></h1>

    <form action="" method="post">
        <input type="hidden" name="form_id" value="cgit_wp_password_protection">
        <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?= esc_html__('Password protection') ?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?= esc_html__('Password protection status') ?></legend>

                        <p>
                            <label>
                                <input type="radio" name="mode" value="disabled" <?= $mode === 'disabled' ? 'checked' : '' ?>>
                                <?= esc_html__('Disabled') ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="radio" name="mode" value="login" <?= $mode === 'login' ? 'checked' : '' ?>>
                                <?= esc_html__('WordPress user account') ?>
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="radio" name="mode" value="password" <?= $mode === 'password' ? 'checked' : '' ?>>
                                <?= esc_html__('Password') ?>
                            </label>
                        </p>

                        <p>
                            <label for="password" class="screen-reader-text"><?= esc_html__('Password') ?></label>
                            <input type="text" name="password" id="password" placeholder="Password" value="<?= esc_attr($password) ?>">
                        </p>
                    </fieldset>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?= esc_html__('Save Changes') ?></button>
        </p>
    </form>
</div>
