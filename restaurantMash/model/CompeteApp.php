<?php

// model
class CompeteApp
{

    private $dbconn;
    private $user;
    private $last_get_results_time = 0;
    private $results_cache;
    private $answered_ten = false;

    public $errors = array();

    // restaurant objects
    public $restaurant1;
    public $restaurant2;

    public function __construct($dbconn, $user, $errors)
    {
        $this->dbconn = $dbconn;
        $this->user = $user;
        $this->errors = $errors;

        $this->get_new_comparison();
        $this->restart_snake_game();
    }

    // store 1 less variable in session
    public function get_user()
    {
        return $this->user;
    }

    public function clear_error_log()
    {
        $this->errors = array();
    }

    public function set_db_connection($dbconn)
    {
        $this->dbconn = $dbconn;
    }

    // choose 2 new restaurants to vote on
    public function get_new_comparison()
    {
        $result = get_comparison($this->dbconn, $this->user, $this->errors);

        $this->restaurant1 = $result[0];
        $this->restaurant2 = $result[1];
    }

    // vote for restaurant 1
    public function win_restaurant_1()
    {
        // if restaurant fetch fails, shouldn't vote, instead just re-fetch
        if ($this->restaurant1 && $this->restaurant2) {
            vote($this->dbconn, $this->restaurant1, RATING::WIN, $this->restaurant2, RATING::LOST, $this->user, $this->errors);
        }
        $this->get_new_comparison();
    }

    // vote for restaurant 2
    public function win_restaurant_2()
    {
        // if restaurant fetch fails, shouldn't vote, instead just re-fetch
        if ($this->restaurant1 && $this->restaurant2) {
            vote($this->dbconn, $this->restaurant2, RATING::WIN, $this->restaurant1, RATING::LOST, $this->user, $this->errors);
        }
        $this->get_new_comparison();
    }

    // vote for "I don't know!!!"
    public function tie()
    {
        // if restaurant fetch fails, shouldn't vote, instead just re-fetch
        if ($this->restaurant1 && $this->restaurant2) {
            vote($this->dbconn, $this->restaurant1, RATING::DRAW, $this->restaurant2, RATING::DRAW, $this->user, $this->errors);
        }
        $this->get_new_comparison();
    }

    // get scores for results page
    public function get_results()
    {
        // if 1 second has elapsed, get new scores
        if (ms() - $this->last_get_results_time > 1000) {
            $this->last_get_results_time = ms();
            $this->results_cache = get_scores($this->dbconn, $this->errors);
        }

        return $this->results_cache;
    }

    // check if user has answered 10 questions
    public function has_answered_ten()
    {
        // if they haven't, double check
        if (!$this->answered_ten) {
            $query = "
              SELECT COUNT(*)
              FROM vote
              WHERE uid = $1;";

            if (!pg_prepare($this->dbconn, "", $query)) {
                $errors[] = pg_last_error();
                return false;
            }

            if (!($result = pg_execute($this->dbconn, "", array($this->user)))) {
                $errors[] = pg_last_error();
                return false;
            }

            if (!($row = pg_fetch_array($result, NULL, PGSQL_NUM))) {
                $errors[] = pg_last_error();
                return false;
            }

            $this->answered_ten = $row[0] >= 10;
        }

        return $this->answered_ten;
    }


    /******************************************
     *               SNAKE GAME               *
     ******************************************/

    // board size
    const SIZE = 5;

    // enum: tile types
    const BLANK = 0;
    const R1 = 1;
    const R2 = 2;
    const HEAD = 3;
    const BODY = 4;

    // enum: state of game
    const STOPPED = 0;
    const STARTED = 1;

    // state
    private $snake_grid;
    private $body_coordinates = array();
    private $restaurant_coordinates = array();
    public $game_state = self::STOPPED;

    // restart the game
    public function restart_snake_game()
    {
        // empty snake grid
        $this->snake_grid = array();
        for ($i = 0; $i < static::SIZE; $i++) {
            $this->snake_grid[] = array();
            for ($j = 0; $j < static::SIZE; $j++) {
                $this->snake_grid[$i][$j] = static::BLANK;
            }
        }

        // random starting location
        $coords = $this->get_empty_coordinates();
        if (count($coords) < 2) $this->lose_game();
        shuffle($coords);

        // random snake location
        $this->snake_grid[$coords[0][0]][$coords[0][1]] = static::HEAD;
        $this->body_coordinates = array($coords[0]);

        // random restaurant locations
        $this->snake_grid[$coords[1][0]][$coords[1][1]] = static::R1;
        $this->snake_grid[$coords[2][0]][$coords[2][1]] = static::R2;
        $this->restaurant_coordinates = array($coords[1], $coords[2]);

        // start game!
        $this->game_state = static::STARTED;
    }

    // remove restaurants from board
    private function remove_restaurants()
    {
        foreach ($this->restaurant_coordinates as $rc) {
            $this->snake_grid[$rc[0]][$rc[1]] = static::BLANK;
        }
        $this->restaurant_coordinates = array();
    }

    // place restaurants in new location on board
    private function place_restaurants()
    {
        // just in case
        $this->remove_restaurants();

        // random locations
        $coords = $this->get_empty_coordinates();
        if (count($coords) < 2) $this->lose_game();
        shuffle($coords);

        $this->snake_grid[$coords[0][0]][$coords[0][1]] = static::R1;
        $this->snake_grid[$coords[1][0]][$coords[1][1]] = static::R2;
        $this->restaurant_coordinates = array($coords[0], $coords[1]);
    }

    // get coordinates of empty squares
    private function get_empty_coordinates()
    {
        $coords = array();
        for ($i = 0; $i < static::SIZE; $i++) {
            for ($j = 0; $j < static::SIZE; $j++) {
                if ($this->snake_grid[$i][$j] == static::BLANK) {
                    $coords[] = array($i, $j);
                }
            }
        }
        return $coords;
    }

    // move in 1 of 4 directions: 'right', 'left', 'up', 'down'
    public function move_in_direction($direction)
    {
        if ($this->game_state != static::STARTED) return;

        $coords = $this->body_coordinates[0];

        // calculate new coordinates
        switch ($direction) {
            case 'right':
                $coords[1] = py_mod($coords[1] + 1, static::SIZE);
                break;
            case 'left':
                $coords[1] = py_mod($coords[1] - 1, static::SIZE);
                break;
            case 'up':
                $coords[0] = py_mod($coords[0] - 1, static::SIZE);
                break;
            case 'down':
                $coords[0] = py_mod($coords[0] + 1, static::SIZE);
                break;
            default:
                $this->errors[] = "INVALID SNAKE DIRECTION: $direction";
                $this->game_state = static::STOPPED;
                return;
        }

        // go to next coordinates and act appropriately
        switch ($this->snake_grid[$coords[0]][$coords[1]]) {
            case static::HEAD:
            case static::BODY:
                $this->lose_game();
                return;
            case static::R1:
                $this->win_restaurant_1();
                $this->remove_restaurants();
                $this->move($coords, true);
                $this->place_restaurants();
                break;
            case static::R2:
                $this->win_restaurant_2();
                $this->remove_restaurants();
                $this->move($coords, true);
                $this->place_restaurants();
                break;
            case static::BLANK:
                $this->move($coords);
                break;
            default:
                $this->errors[] = "INVALID SNAKE BODY STATE: " . $this->snake_grid[$coords[0]][$coords[1]];
                $this->game_state = static::STOPPED;
                return;
        }
    }

    // end the game
    private function lose_game()
    {
        $this->game_state = static::STOPPED;
    }

    // move snake to coordinates (should only be 1 square away from head)
    private function move($coords, $extend_body = false)
    {
        $this->snake_grid[$coords[0]][$coords[1]] = static::HEAD;
        $this->snake_grid[$this->body_coordinates[0][0]][$this->body_coordinates[0][1]] = static::BODY;

        // move each bodypart to its next bodypart's location
        $last_coords = $coords;
        for ($i = 0; $i < count($this->body_coordinates); $i++) {
            // update body coordinates
            $tmp = $this->body_coordinates[$i];
            $this->body_coordinates[$i] = $last_coords;
            $last_coords = $tmp;
        }

        // add an extra body part at the end if required
        if ($extend_body) {
            $this->body_coordinates[] = $last_coords;
        } // otherwise leave it empty
        else {
            $this->snake_grid[$last_coords[0]][$last_coords[1]] = static::BLANK;
        }
    }

    // board colors
    private $board_color_map = array(
        self::BLANK => 'lightgray',
        self::R1 => 'red',
        self::R2 => 'red',
        self::HEAD => 'darkblue',
        self::BODY => 'blue'
    );

    // board text
    private $board_text_map = array(
        self::BLANK => '',
        self::R1 => 'R1',
        self::R2 => 'R2',
        self::HEAD => '',
        self::BODY => ''
    );

    // draw snake board
    public function draw_board()
    {
        $board = "<table>\n";
        foreach ($this->snake_grid as $row) {
            $board .= "<tr>\n";
            foreach ($row as $cell) {
                $board .= '<td class="snake-tile" style="background-color: ' . $this->board_color_map[$cell] . ';">';

                if ($this->game_state == static::STOPPED) {
                    $board .= '☠';
                } else {
                    $board .= $this->board_text_map[$cell];
                }

                $board .= "</td>\n";
            }
            $board .= "</tr>\n";
        }
        $board .= "</table>\n";
        return $board;
    }

    // get current game state
    public function get_snake_game_state()
    {
        return $this->game_state;
    }

}

?>

