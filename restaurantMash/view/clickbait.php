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
                <button type="submit" name="operation" value="snake">Snake</button>
            <li>
                <button type="submit" class="selected" name="operation" value="clickbait">Clickbait</button>
            <li>
                <button type="submit" name="operation" value="logout">Logout</button>
        </ul>
    </form>
</nav>
<main>
    <?php display_errors_if_available($errors) ?>
    <h1>Clickbait Articles</h1>
    Redacted page.
</main>
<footer>
</footer>
</body>
</html>

