<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" http-equiv="refresh" content="30">
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
                <button type="submit" class="selected" name="operation" value="results">Results</button>
            <li>
                <button type="submit" name="operation" value="user_profile">User Profile</button>
            <li>
                <button type="submit" name="operation" value="snake">Snake</button>
            <li>
                <button type="submit" name="operation" value="clickbait">Clickbait</button>
            <li>
                <button type="submit" name="operation" value="logout">Logout</button>

        </ul>
    </form>
</nav>
<main>
    <?php display_errors_if_available(array_merge($errors, $_SESSION['app']->errors)) ?>
    <h1>Results</h1>
    <table>
        <tr>
            <th>Restaurant</th>
            <th>Rating</th>
            <th>Velocity</th>
            <th>Wins</th>
            <th>Losses</th>
            <th>Ties</th>
        </tr>
        <?php

        foreach ($_SESSION['app']->get_results() as $result) {
            echo '<tr>';
            echo '<td>' . $result['rname'] . '</td>';
            echo '<td>' . sprintf("%.2f", $result['score']) . '</td>';
            echo '<td>' . sprintf("%.2f", $result['velocity']) . '</td>';
            echo '<td>' . $result['wins'] . '</td>';
            echo '<td>' . $result['losses'] . '</td>';
            echo '<td>' . $result['ties'] . '</td>';
            echo '</tr>';
        }

        ?>
    </table>
</main>
<footer>
</footer>
</body>
</html>

