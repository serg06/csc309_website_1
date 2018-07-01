<?php
require_once "dbconnect_string.php";
require_once "Rating.php"; // Rating.php is from https://github.com/Chovanec/elo-rating/blob/master/src/Rating/Rating.php
require_once "model/Restaurant.php";

// connect to DB
function db_connect()
{
    global $g_dbconnect_string;
    $dbconn = pg_connect($g_dbconnect_string);
    if (!$dbconn) {
        $system_errors[] = "Can't connect to the database.";
        return null;
    } else return $dbconn;
}

// login
// return true if succeeded, false if failed
function login($dbconn, $user, $pass, & $errors = array())
{
    $query = "SELECT * FROM appuser WHERE id=$1 AND password=MD5($2);";

    if (!pg_prepare($dbconn, "", $query)) {
        $errors[] = "Login error (pg_prepare): " . pg_last_error();
        return false;
    }

    if (!($result = pg_execute($dbconn, "", array($user, $pass)))) {
        $errors[] = "Login error (pg_execute): " . pg_last_error();
        return false;
    }

    if (!pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
        $errors[] = "invalid login: " . pg_last_error();
        return false;
    }

    return true;
}

// create user
// return true if succeeded, false if failed
function create_user($dbconn, $user, $pass, $first_name, $last_name, $age, & $errors = array())
{
    // whitelist $user just in case, since it's the most dangerous
    $non_whitelisted_field = get_non_whitelisted_field(array('user' => $user));
    if ($non_whitelisted_field) {
        $errors[] = "field not matched by whitelist (a-zA-Z0-9): " . $non_whitelisted_field;
        return false;
    }

    $query = "INSERT INTO appuser(id, password, firstName, lastName, age) VALUES ($1, MD5($2), $3, $4, $5);";

    if (!pg_prepare($dbconn, "", $query)) {
        $errors[] = "Create user error (pg_prepare): " . pg_last_error();
        return false;
    }

    if ($age < 0 || $age > 100) {
        $errors[] = "Age [$age] violates constraint: 0 < age < 100";
        return false;
    }

    if (!($result = pg_execute($dbconn, "", array($user, $pass, $first_name, $last_name, $age)))) {
        $errors[] = "User already exists!: " . pg_last_error();
        return false;
    }

    return true;
}

// update user
// return true if succeeded, false if failed
function update_user($dbconn, $user, $first_name, $last_name, $age, & $errors = array())
{
    $query = "
      UPDATE appuser
      SET firstName = $2, lastName = $3, age = $4
      WHERE id = $1";

    if (!pg_prepare($dbconn, "", $query)) {
        $errors[] = "Update user error (pg_prepare): " . pg_last_error();
        return false;
    }

    if ($age < 0 || $age > 100) {
        $errors[] = "Age [$age] violates constraint: 0 < age < 100";
        return false;
    }

    if (!($result = pg_execute($dbconn, "", array($user, $first_name, $last_name, $age)))) {
        $errors[] = "Update user error (pg_execute): " . pg_last_error();
        return false;
    }

    return true;
}

// get user's profile
function get_user($dbconn, $user, & $errors)
{
    // select all but password
    $query = "
        SELECT id, firstName, lastName, age
        FROM appuser
        WHERE id = $1";

    if (!pg_prepare($dbconn, "", $query)) {
        $errors[] = "Get user error (pg_prepare): " . pg_last_error();
        return null;
    }

    if (!($result = pg_execute($dbconn, "", array($user)))) {
        $errors[] = "Get user error (pg_execute): " . pg_last_error();
        return null;
    }

    if (!($row = pg_fetch_row($result, NULL, PGSQL_ASSOC))) {
        $errors[] = "Get user error (pg_fetch_row): " . pg_last_error();
        return null;
    }

    return $row;
}

// get 2 restaurants to compare from the DB
function get_comparison($dbconn, $user, & $errors)
{
    // select random 2 restaurants, which the user hasn't voted on, and which have among the smallest score difference
    $query = "
        SELECT rid1, rname1, rid2, rname2
        FROM (
          -- sort top 100 closest-score pairs by score difference
          SELECT r1.rid rid1, r1.rname rname1, r2.rid rid2, r2.rname rname2
          FROM restaurant r1
          INNER JOIN restaurant r2 ON (r1.rid < r2.rid)
          AND NOT EXISTS (
            -- select pairs r1, r2 which the user has voted on
            SELECT *
            FROM vote v
            WHERE v.uid = $1
            AND v.rid1 = r1.rid
            AND v.rid2 = r2.rid
          )
          ORDER BY ABS(r1.score - r2.score) ASC
          LIMIT 100
        ) pairs
        ORDER BY RANDOM()
        LIMIT 1;";

    if (!pg_prepare($dbconn, "", $query)) {
        $errors[] = "Get comparison error (pg_prepare): " . pg_last_error();
        return false;
    }

    if (!($result = pg_execute($dbconn, "", array($user)))) {
        $errors[] = "Get comparison error (pg_execute): " . pg_last_error();
        return false;
    }

    if (!($row = pg_fetch_array($result, NULL, PGSQL_ASSOC))) {
        $errors[] = "No comparisons remaining (???): " . pg_last_error();
        return false;
    }

    $r1 = new Restaurant($row['rid1'], $row['rname1']);
    $r2 = new Restaurant($row['rid2'], $row['rname2']);

    return array($r1, $r2);
}

// vote on 2 restaurant( object)s
function vote($dbconn, $player1, $player1_result, $player2, $player2_result, $user, & $errors)
{
    // prepare add-vote query
    $query = "INSERT INTO vote VALUES ($1, $2, $3)";
    if (!pg_prepare($dbconn, "addVote", $query)) {
        $errors[] = pg_last_error();
        return false;
    }

    // prepare get-score query
    $query = "SELECT score, velocity, wins, losses, ties FROM restaurant WHERE rid = $1";
    if (!pg_prepare($dbconn, "getScore", $query)) {
        $errors[] = pg_last_error();
        return false;
    }

    // prepare update-score query
    $query = "
      UPDATE restaurant
      SET score = $2,
          velocity = (velocity + ($2 - restaurant.score))/2,
          wins = $3,
          losses = $4,
          ties = $5
      WHERE rid = $1";
    if (!pg_prepare($dbconn, "updateScore", $query)) {
        $errors[] = pg_last_error();
        return false;
    }

    // we need to know which ID is smaller b/c of how votes are stored in db
    $smaller_id = min($player1->id, $player2->id);
    $larger_id = max($player1->id, $player2->id);

    // try 3 times just in case
    for ($tries = 0; $tries < 3; $tries++) {
        try {
            // begin transaction
            pg_query($dbconn, "BEGIN");

            // add vote
            if (!($result = pg_execute($dbconn, "addVote", array($smaller_id, $larger_id, $user)))) {
                throw new Exception(pg_last_error());
            }

            // get player1's score
            if (!($result = pg_execute($dbconn, "getScore", array($player1->id)))) {
                throw new Exception(pg_last_error());
            }

            if (!($player1_row = pg_fetch_array($result, NULL, PGSQL_ASSOC))) {
                throw new Exception("Player1 has no score???: " . pg_last_error());
            }

            // get player2's score
            if (!($result = pg_execute($dbconn, "getScore", array($player2->id)))) {
                throw new Exception(pg_last_error());
            }

            if (!($player2_row = pg_fetch_array($result, NULL, PGSQL_ASSOC))) {
                throw new Exception("Player2 has no score??: " . pg_last_error());
            }

            // calculate new scores
            $rating = new Rating($player1_row['score'], $player2_row['score'], $player1_result, $player2_result);
            $result = $rating->getNewRatings();

            // save results (format: rid, score, wins, losses, ties)
            $player1_update = array(
                $player1->id,
                $result['a'],
                $player1_row['wins'] + ($player1_result == Rating::WIN),
                $player1_row['losses'] + ($player1_result == Rating::LOST),
                $player1_row['ties'] + ($player1_result == Rating::DRAW)
            );

            $player2_update = array(
                $player2->id,
                $result['b'],
                $player2_row['wins'] + ($player2_result == Rating::WIN),
                $player2_row['losses'] + ($player2_result == Rating::LOST),
                $player2_row['ties'] + ($player2_result == Rating::DRAW)
            );

            // update player1's score
            if (!($result = pg_execute($dbconn, "updateScore", $player1_update))) {
                throw new Exception(pg_last_error());
            }

            // update player2's score
            if (!($result = pg_execute($dbconn, "updateScore", $player2_update))) {
                throw new Exception(pg_last_error());
            }

            // commit!!!
            if (!pg_query($dbconn, "COMMIT")) {
                throw new Exception("Failed to commit: " . pg_last_error());
            }

            // success!
            return true;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
            pg_query("ROLLBACK");
            continue;
        }
    }

    $errors[] = "Failed to vote after 3 attempts.";
    return false;
}

// get all scores from db
function get_scores($dbconn, & $errors)
{
    $query = "SELECT * FROM restaurant r;";

    if (!($result = pg_query($dbconn, $query))) {
        $errors[] = "Cannot load scores: " . pg_last_error();
        return array();
    }

    if (!($result = pg_fetch_all($result))) {
        $errors[] = "Cannot fetch all scores: " . pg_last_error();
        return array();
    }

    return $result;
}

// get a local(?) millisecond timestamp
function ms()
{
    return round(microtime(true) * 1000);
}

// return the first field that array $a is missing
function get_missing_field($a, $fields)
{
    foreach ($fields as $field) {
        if (empty($a[$field])) {
            return $field;
        }
    }

    return null;
}

// return the first field in an array $a which doesn't match our whitelist (a-zA-Z0-9)
function get_non_whitelisted_field($a)
{
    foreach ($a as $key => $value) {
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $value)) {
            return "$key => $value";
        }
    }

    return null;
}

// a % b as python does it (in php, (-3 % 4) == (3 % 4) == 3)
function py_mod($a, $b)
{
    return (($a % $b) + $b) % $b;
}

// print errors if there are any
function display_errors_if_available($errors)
{
    if (!empty($errors)) {
        echo '<table style="width: 100%;">';
        echo '<tr><td style="color: darkred; font-size: 20px; background-color: lightgray; text-align: center;">Errors:</td></tr>';

        foreach ($errors as $error) {
            echo '<tr><td style="background-color: #f2f2fd; text-align: center;">' . $error . '</td></tr>';
        }

        echo '</table>';
    }
}

?>
