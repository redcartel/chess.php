<?php
/*
 * Carter's Chess Game
 * Made in 2019 for the public domain
 *
 * JSON web API for React game frontend
 *
 * TODO: lots! Multiple boards, en passant and castling, check and checkmate
 * maybe A.I.? Leaderboard? User accounts? Real chess notation used internally?
 * The sky is the limit
 */

error_log('chess.php start');

// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, POST, HEAD");
header("Content-Type: applicaiton/json");

include 'dbconnect.php';
include 'chessgame.php';

$board = new ChessGame();

/* LOAD CURRENT BOARD IF ONE EXISTS */
$sql = "SELECT board FROM positions ORDER BY pk DESC LIMIT 1";
$statement = $pdo->query($sql);
$row = $statement->fetch();
if ($row) {
    $board->setFromStr($row[0]);
}

/* ON VERY FIRST EXECUTION, CREATE NEW BOARD IN FRESH DB */
else {
    $board->writeToDB();
}

/* RESPOND TO GET REQUEST WITH BOARD STATE */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo $board->toJSON();
    exit();
}

/* PUT REQUESTS ARE MOVE OR BOARD RESET ACTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $args = [];
    parse_str($_SERVER['QUERY_STRING'], $args);
    
    /* IF MOVE COORDS ARE 'x' THAT IS A REQUEST TO RESET THE BOARD */
    if ($args['rf'] === 'x') {
        $board = new ChessGame(); 
        $board->writeToDB();
    }
    
    if ($args['turn'] === "$board->turn") {
        /* ONLY MOVE IF THE USER AND THE BOARD ARE ON THE SAME TURN */
        $rf = (int)$args['rf']; // row from 
        $cf = (int)$args['cf']; // col from
        $rt = (int)$args['rt']; // row to
        $ct = (int)$args['ct']; // col to
        if ($board->legalMove($rf, $cf, $rt, $ct)) {
            $board->move($rf, $cf, $rt, $ct);
            $board->turn++;
            $board->writeToDB();
        }
    }
    /* RESPOND WITH BOARD STATE */
    echo $board->toJSON();
    exit();
}
?>
