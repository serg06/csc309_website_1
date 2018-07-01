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
                <button type="submit" class="selected" name="operation" value="compete">Compete</button>
            <li>
                <button type="submit" name="operation" value="results">Results</button>
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
    <h1>Compete!</h1>
    <h3>Which restaurant is better?</h3>
    <form method="post">
        <table>
            <tr>
                <th style="text-align: center;"><?php echo $_SESSION['app']->restaurant1->name; ?></th>
                <td style="text-align: center;">VS.</td>
                <th style="text-align: center;"><?php echo $_SESSION['app']->restaurant2->name; ?></th>
            </tr>
            <tr>
                <td style="text-align: center;">
                    <button type="submit" name="submit" value="restaurant1">Choose</button>
                </td>
                <td style="text-align: center;">
                    <button type="submit" name="submit" value="tie">I don't know!!!</button>
                </td>
                <td style="text-align: center;">
                    <button type="submit" name="submit" value="restaurant2">Choose</button>
                </td>
            </tr>
        </table>
        <input type="hidden" name="page_token" value="<?php echo $_SESSION['page_token'] ?>"/>
    </form>
</main>
<footer>
</footer>
</body>
</html>

