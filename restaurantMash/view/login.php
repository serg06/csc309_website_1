<?php
// So we don't have to deal with unset fields when re-filling
isset($_REQUEST['user']) or $_REQUEST['user'] = '';
isset($_REQUEST['password']) or $_REQUEST['password'] = '';
isset($_REQUEST['first_name']) or $_REQUEST['first_name'] = '';
isset($_REQUEST['last_name']) or $_REQUEST['last_name'] = '';
isset($_REQUEST['age']) or $_REQUEST['age'] = '';
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
<main>
    <?php display_errors_if_available($errors) ?>
    <h1>Login</h1>
    <fieldset>
        <legend>Login</legend>
        <form method="post">
            <table>
                <!-- Trick below to re-fill the user form field -->
                <tr>
                    <th><label>User</label></th>
                    <td><input type="text" name="user" value="<?php echo($_REQUEST['user']); ?>" required
                               pattern="[a-zA-Z0-9]+" title="only alphanumeric characters are allowed"/></td>
                </tr>
                <tr>
                    <th><label>Password</label></th>
                    <td><input type="password" name="password" required/></td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <button type="submit" name="submit" value="login">Login</button>
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>
    <fieldset>
        <legend>New User</legend>
        <form method="post">
            <table>
                <tr>
                    <th><label>User</label></th>
                    <td><input type="text" name="user" value="<?php echo($_REQUEST['user']); ?>" required
                               pattern="[a-zA-Z0-9]+" title="only alphanumeric characters are allowed"/></td>
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
                    <th></th>
                    <td>
                        <button type="submit" name="submit" value="new_user">New User</button>
                    </td>
                </tr>
            </table>
        </form>

    </fieldset>

</main>
<footer>
</footer>
</body>
</html>

