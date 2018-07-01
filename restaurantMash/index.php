<?php
//ini_set('display_errors', 'On');

require_once "lib/lib.php";
require_once "model/CompeteApp.php";

session_save_path("sess");
session_start();

$dbconn = db_connect();

// app functionality basics
if (isset($_SESSION['app'])) {
    $_SESSION['app']->set_db_connection($dbconn);
    $_SESSION['app']->clear_error_log();
}

$errors = array();
$view = "";

/* controller code */
isset($_SESSION['state']) OR $_SESSION['state'] = 'login';
isset($_SESSION['logged_in']) OR $_SESSION['logged_in'] = false;
isset($_SESSION['page_token']) OR $_SESSION['page_token'] = rand();

// perform operation
if (isset($_REQUEST['operation'])) {
    switch ($_REQUEST['operation']) {
        // login not required
        case "logout":
            session_destroy();
            session_start();
            $_SESSION['state'] = 'login';
            break;

        // login required
        case "snake":
        case "compete":
        case "results":
        case "user_profile":
        case "clickbait":
            if ($_SESSION['logged_in']) {
                $_SESSION['state'] = $_REQUEST['operation'];
            }
            break;

        // invalid operation
        default:
            $op = $_REQUEST['operation'];
            $errors[] = "invalid operation: $op";
            break;
    }
}

// states/views
switch ($_SESSION['state']) {
    // logging in
    case "login":
        $view = "login.php";

        // skip if invalid submit
        if (empty($_REQUEST['submit']) || !in_array($_REQUEST['submit'], array('login', 'new_user'))) {
            break;
        }

        if (!$dbconn) break;

        switch ($_REQUEST['submit']) {
            // handle new user request
            case 'new_user':
                $missing_field = get_missing_field($_REQUEST, array('user', 'password', 'first_name', 'last_name', 'age'));
                if ($missing_field) {
                    $errors[] = "missing required field: " . $missing_field;
                    break;
                }

                $non_whitelisted_field = get_non_whitelisted_field($_REQUEST);
                if ($non_whitelisted_field) {
                    $errors[] = "field not matched by whitelist (a-zA-Z0-9): " . $non_whitelisted_field;
                    break;
                }

                create_user($dbconn, $_REQUEST['user'], $_REQUEST['password'], $_REQUEST['first_name'], $_REQUEST['last_name'], $_REQUEST['age'], $errors);
                break;

            // handle login request
            case 'login':
                $missing_field = get_missing_field($_REQUEST, array('user', 'password'));
                if ($missing_field) {
                    $errors[] = "missing required field: " . $missing_field;
                    break;
                }

                $non_whitelisted_field = get_non_whitelisted_field($_REQUEST);
                if ($non_whitelisted_field) {
                    $errors[] = "field not matched by whitelist (a-zA-Z0-9): " . $non_whitelisted_field;
                    break;
                }

                if (login($dbconn, $_REQUEST['user'], $_REQUEST['password'], $errors)) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['state'] = 'compete';
                    $_SESSION['app'] = new CompeteApp($dbconn, $_REQUEST['user'], $errors);
                    $view = "compete.php";
                }
                break;

            default:
                $errors[] = "login: invalid submit request: " . $_REQUEST['submit'];
                break;
        }

        break;


    // competing
    case "compete":
        $view = "compete.php";

        // skip if not submit
        if (empty($_REQUEST['submit'])) {
            break;
        }

        // rely on app to exist
        if (!isset($_SESSION['app'])) break;

        // page token
        if ($_SESSION['page_token'] != $_REQUEST['page_token']) {
            $errors[] = "Warning: Ignoring request (invalid page token)";
            break;
        }

        // handle submit requests
        switch ($_REQUEST['submit']) {
            case 'restaurant1':
                $_SESSION['app']->win_restaurant_1();
                break;
            case 'restaurant2':
                $_SESSION['app']->win_restaurant_2();
                break;
            case 'tie':
                $_SESSION['app']->tie();
                break;
            default:
                $errors[] = "invalid compete submit option: " . $_REQUEST['submit'];
        }
        break;

    // results page
    case "results":
        if ($_SESSION['app']->has_answered_ten()) {
            $view = "results.php";
        } else {
            $view = "results_10.php";
        }
        break;

    // user profile page
    case "user_profile":
        $view = "user_profile.php";

        // skip if invalid submit
        if (empty($_REQUEST['submit']) || $_REQUEST['submit'] != 'update_profile') {
            break;
        }

        // perform operation, switching state and view if necessary
        if (!$dbconn) break;

        $missing_field = get_missing_field($_REQUEST, array('password', 'first_name', 'last_name', 'age'));
        if ($missing_field) {
            $errors[] = "missing required field: " . $missing_field;
            break;
        }

        $non_whitelisted_field = get_non_whitelisted_field($_REQUEST);
        if ($non_whitelisted_field) {
            $errors[] = "field not matched by whitelist (a-zA-Z0-9): " . $non_whitelisted_field;
            break;
        }

        update_user($dbconn, $_SESSION['app']->get_user(), $_REQUEST['first_name'], $_REQUEST['last_name'], $_REQUEST['age'], $errors);
        break;

    case "snake":
        $view = "snake.php";

        // skip if invalid submit
        if (empty($_REQUEST['submit']) || !in_array($_REQUEST['submit'], array('restart', 'right', 'left', 'up', 'down'))) {
            break;
        }

        // handle submit request
        switch ($_REQUEST['submit']) {
            case 'restart':
                $_SESSION['app']->restart_snake_game();
                break;

            case 'right':
            case 'left':
            case 'up':
            case 'down':
                $_SESSION['app']->move_in_direction($_REQUEST['submit']);
                break;

            default:
                $errors[] = "Invalid snake option: " . $_REQUEST['submit'];
        }

        break;

    case "clickbait":
        $view = 'clickbait.php';
        break;

    default:
        $errors[] = "No state found called " . $_SESSION['state'];
        break;
}

// just in case!
if ($view == "") {
    $errors[] = 'No view selected!';
    display_errors_if_available($errors);
}

// regenerate page token
$_SESSION['page_token'] = rand();

require_once "view/$view";

?>
