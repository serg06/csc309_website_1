<?php

// when we need a restaurant's name, we usually need its id too
class Restaurant
{
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

?>

