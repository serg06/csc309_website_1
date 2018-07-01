<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <title>RestaurantMash</title>
</head>
<body>
<header><h1>RestaurantMash</h1></header>
<nav>
    <form method="post">
        <ul>
            <li>
                <button type="submit" name="operation" value="compete">Compete</button>
            <li>
                <button type="submit" name="operation" value="results">Results</button>
            <li>
                <button type="submit" name="operation" value="user_profile">User Profile</button>
            <li>
                <button type="submit" class="selected" name="operation" value="snake">Snake</button>
            <li>
                <button type="submit" name="operation" value="clickbait">Clickbait</button>
            <li>
                <button type="submit" name="operation" value="logout">Logout</button>

        </ul>
    </form>
</nav>
<main>
    <?php display_errors_if_available(array_merge($errors, $_SESSION['app']->errors)) ?>
    <table>
        <tr style="text-align: center;">
            <td style="font-size: 30px">Restaurant Snake!</td>
        </tr>
        <tr style="text-align: center;">
            <td style="font-size: 20px">Eat a restaurant to vote for it!</td>
        </tr>
        <tr>
            <td>
                <table>
                    <tr style="text-align: center;">
                        <td>
                            <table>
                                <tr>
                                    <th style="text-align: center;">Restaurant R1</th>
                                    <th style="text-align: center;">Restaurant R2</th>
                                </tr>
                                <tr>
                                    <td style="text-align: center;"><?php echo $_SESSION['app']->restaurant1->name ?></td>
                                    <td style="text-align: center;"><?php echo $_SESSION['app']->restaurant2->name ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="text-align: center;">
                        <td>
                            <?php echo $_SESSION['app']->draw_board() ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <form method="post">
                    <table>
                        <tr>
                            <td class="snake-button"></td>
                            <td class="snake-button">
                                <button type="submit" name="submit" value="up"
                                        class="snake-button" <?php if ($_SESSION['app']->get_snake_game_state() == CompeteApp::STOPPED) echo 'disabled' ?>>
                                    <h1>🢁</h1></button>
                            </td>
                            <td class="snake-button"></td>
                        </tr>
                        <tr>
                            <td class="snake-button">
                                <button type="submit" name="submit" value="left"
                                        class="snake-button" <?php if ($_SESSION['app']->get_snake_game_state() == CompeteApp::STOPPED) echo 'disabled' ?>>
                                    <h1>🢀</h1></button>
                            </td>
                            <td class="snake-button">
                                <button type="submit" name="submit" value="restart"
                                        class="snake-button"><h1>↺</h1></button>
                            </td>
                            <td class="snake-button">
                                <button type="submit" name="submit" value="right"
                                        class="snake-button" <?php if ($_SESSION['app']->get_snake_game_state() == CompeteApp::STOPPED) echo 'disabled' ?>>
                                    <h1>🢂</h1></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="snake-button"></td>
                            <td class="snake-button">
                                <button type="submit" name="submit" value="down"
                                        class="snake-button" <?php if ($_SESSION['app']->get_snake_game_state() == CompeteApp::STOPPED) echo 'disabled' ?>>
                                    <h1>🢃</h1></button>
                            </td>
                            <td class="snake-button"></td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
</main>
<footer>
</footer>
</body>
</html>

