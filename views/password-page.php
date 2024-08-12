<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Access Denied</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                background: white;
                color: black;
                font-family: sans-serif;
                line-height: 1.5;
                margin: 0;
                padding: 1rem;
            }

            main {
                align-items: center;
                display: flex;
                justify-content: center;
                min-height: 75vh;
            }

            form {
                max-width: 100%;
                width: 25rem;
            }

            label {
                display: block;
                margin-bottom: 1rem;
                text-align: center;
            }

            input,
            button {
                font: inherit;
                line-height: inherit;
            }

            input {
                background: white;
                border: 1px solid #ccc;
                border-radius: 0.25rem;
                color: inherit;
                display: block;
                padding: 0.5em;
                width: 100%;
            }

            input:focus {
                border-color: #369;
                outline: 0.25rem solid #3698;
            }

            button {
                border: 0;
                border-radius: 0.25rem;
                color: white;
                cursor: pointer;
                background: #369;
                padding: 0.5em 1em;
                transition: background-color 200ms;
            }

            button:focus {
                outline: 0.25rem solid #3698;
            }

            button:hover {
                background: #47a;
            }

            .group {
                display: flex;
                align-items: stretch;
            }

            .group input {
                border-radius: 0.25rem 0 0 0.25rem;
            }

            .group button {
                border-radius: 0 0.25rem 0.25rem 0;
            }

            .message {
                border: 1px solid;
                border-radius: 0.25rem;
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .message.error {
                background: #c331;
                border-color: #c334;
                color: #c33;
            }
        </style>
    </head>

    <body>
        <main>
            <form action="" method="post">
                <?php

                if ($error) {
                    ?>
                    <div class="message error">
                        <?= $error ?>
                    </div>
                    <?php
                }

                ?>

                <label for="password">Enter your password to view the site</label>

                <div class="group">
                    <input type="password" name="password" id="password" placeholder="Password">
                    <button type="submit" name="cgit_wp_password_protection_submit" value="1">Enter</button>
                </div>
            </form>
        </main>
    </body>
</html>
