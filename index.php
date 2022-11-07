<?php


    require_once('./classes/Player.php');
    require_once('./classes/Game.php');

    $player1 = new Player('Sylvain');
    $player2 = new Player('Karolina');
    $game = new Game($player1, $player2);

?>