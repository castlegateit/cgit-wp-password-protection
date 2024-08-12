<?php

if (!isset($nonce) || !isset($mode) || !isset($password)) {
    return;
}

?>

<div class="wrap">
    <h1>Password Protection</h1>

    <form action="" method="post">
        <input type="hidden" name="form_id" value="cgit_wp_password_protection">
        <input type="hidden" name="nonce" value="<?= esc_attr($nonce) ?>">

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Password protection</th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text">Password protection status</legend>

                        <p>
                            <label>
                                <input type="radio" name="mode" value="disabled" <?= $mode === 'disabled' ? 'checked' : '' ?>>
                                Disabled
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="radio" name="mode" value="login" <?= $mode === 'login' ? 'checked' : '' ?>>
                                WordPress user account
                            </label>
                        </p>

                        <p>
                            <label>
                                <input type="radio" name="mode" value="password" <?= $mode === 'password' ? 'checked' : '' ?>>
                                Password
                            </label>
                        </p>

                        <p>
                            <label for="password" class="screen-reader-text">Password</label>
                            <input type="text" name="password" id="password" placeholder="Password" value="<?= esc_attr($password) ?>">
                        </p>
                    </fieldset>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">Save Changes</button>
        </p>
    </form>
</div>
