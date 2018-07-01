<?php
isset($_REQUEST['password']) or $_REQUEST['password'] = '';

// if submit, re-fill with submitted fields
if (!empty($_REQUEST['submit']) && $_REQUEST['submit'] == 'update_profile') {
    isset($_REQUEST['first_name']) or $_REQUEST['first_name'] = '';
    isset($_REQUEST['last_name']) or $_REQUEST['last_name'] = '';
    isset($_REQUEST['age']) or $_REQUEST['age'] = '';
} // otherwise, re-fill with actual user profile fields!
else {
    if ($user = get_user($dbconn, $_SESSION['app']->get_user(), $errors)) {
        $_REQUEST['first_name'] = $user['firstname'];
        $_REQUEST['last_name'] = $user['lastname'];
        $_REQUEST['age'] = $user['age'];
    }
}

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
                <button type="submit" class="selected" name="operation" value="user_profile">User Profile</button>
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
    <?php display_errors_if_available($errors) ?>
    <h1>Edit User Profile:</h1>
    <form method="post">
        <table>
            <tr>
                <th><label>User</label></th>
                <td><input name="user" value="<?php echo($_SESSION['app']->get_user()); ?>" disabled/></td>
            </tr>
            <tr>
                <th><label>Password</label></th>
                <td><input type="password" name="password" required/></td>
            </tr>
            <tr>
                <th><label>First name</label></th>
                <td><input type="text" name="first_name" value="<?php echo($_REQUEST['first_name']); ?>" required
                           pattern="[a-zA-Z0-9]+" title="only alphanumeric characters are allowed"/></td>
            </tr>
            <tr>
                <th><label>Last name</label></th>
                <td><input type="text" name="last_name" value="<?php echo($_REQUEST['last_name']); ?>" required
                           pattern="[a-zA-Z0-9]+" title="only alphanumeric characters are allowed"/></td>
            </tr>
            <tr>
                <th><label>Age</label></th>
                <td><select name="age" size="5" required>
                        <?php
                        for ($i = 1; $i <= 100; $i++) {
                            $selected = ($i == $_REQUEST['age'] ? 'selected' : '');
                            echo "<option value=\"$i\" $selected>$i</option>\n";
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>
                    <button type="submit" name="submit" value="update_profile">Update</button>
                </td>
            </tr>
        </table>
    </form>
</main>
<footer>
</footer>
</body>
</html>

