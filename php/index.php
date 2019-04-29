<?php
error_log('ok');

header("Access-Control-Allow-Origin: *");
header("Content-Type: applicaiton/json");
header("Access-Control-Allow-Methods: *");

$pdo = new PDO("mysql:host=127.0.0.1;dbname=chess", "redcartel", "redring9");


class ChessGame {

    public $squares;
    public $turn;
    public $info;

    function __construct($text=Null) {
        if ($text !== Null) {
            $this->setFromStr($text);
        }
        else {
            $this->info = [];
            $this->squares = array();
            array_push($this->squares, array('Br','Bk','Bb','BQ','BK','Bb','Bk','Br'));
            array_push($this->squares, array('Bp','Bp','Bp','Bp','Bp','Bp','Bp','Bp'));
            for ($i = 0; $i < 4; $i++) { 
                array_push($this->squares, array('','','','','','','',''));
            }
            array_push($this->squares, array('wp','wp','wp','wp','wp','wp','wp','wp'));
            array_push($this->squares, array('wr','wk','wb','wQ','wK','wb','wk','wr'));


            $this->turn = 0;
        }
    }

    function legalMove($rowFrom, $colFrom, $rowTo, $colTo) {

        if ($this->victory() !== '') {
            error_log('victoryFalse');
            return False;
        }

        $turnColor = $this->turn % 2 === 0 ? 'w' : 'B';

        if ($turnColor !== $this->color($rowFrom, $colFrom)) {
            error_log('wrongTurn');
            return False;
        }

        $moves = $this->getMoves($rowFrom, $colFrom);
        if ($this->moveInList($rowTo, $colTo, $moves)) {
            return True;
        }

        error_log('noListFalse');
        return False;
    }

    function color($r, $c) {
        $str = $this->squares[$r][$c];
        if (strlen($str) !== 2) {
            return False;
        }
        return substr($str,0,1);
    }

    function piece($r, $c) {
        $str = $this->squares[$r][$c];
        if (strlen($str) !== 2) {
            return False;
        }
        return substr($str,1,1);
    }

    function getMoves($r, $c) {
        if (!$this->color($r, $c)) {
            return [];
        }
        if ($this->piece($r, $c) === 'p') {
            return $this->pawnMoves($r, $c, $this->color($r, $c));
        }
        if ($this->piece($r, $c) === 'r') {
            return $this->rookMoves($r, $c, $this->color($r, $c));
        }
        if ($this->piece($r, $c) === 'b') {
            return $this->bishopMoves($r, $c, $this->color($r, $c));
        }
        if ($this->piece($r, $c) === 'Q') {
            return $this->queenMoves($r, $c, $this->color($r, $c));
        }
        if ($this->piece($r, $c) === 'k') {
            return $this->knightMoves($r, $c, $this->color($r, $c));
        }
        if ($this->piece($r, $c) === 'K') {
            return $this->kingMoves($r, $c, $this->color($r, $c));
        }
        return [];
    }

    function pawnMoves($r, $c, $color) {
        $moves = [];
        if ($color === 'w') {
            if ($r === 6) {
                if ($this->squares[$r-1][$c] === '' && $this->squares[$r-2][$c] === '') {
                    array_push($moves, [$r-2, $c]);
                }
            }
            if ($r > 0 && $this->squares[$r-1][$c] === '') {
                array_push($moves, [$r-1, $c]);
            }
            if ($c > 0 && $this->color($r-1, $c-1) === 'B') {
                array_push($moves, [$r-1, $c-1]);
            }
            if ($c < 7 && $this->color($r-1, $c+1) === 'B') {
                array_push($moves, [$r-1, $c+1]);
            }
        }
        if ($color === 'B') {
            if ($r === 1) {
                if ($this->squares[$r+1][$c] === '' && $this->squares[$r+2][$c] === '') {
                    array_push($moves, [$r+2, $c]);
                }
            }
            if ($r < 7 && $this->squares[$r+1][$c] === '') {
                array_push($moves, [$r+1, $c]);
            }
            if ($c > 0 && $r < 7 && $this->color($r+1, $c-1) === 'w') {
                array_push($moves, [$r+1, $c-1]);
            }
            if ($c < 7 && $r < 7 && $this->color($r+1, $c+1) === 'w') {
                array_push($moves, [$r+1, $c+1]);
            }
        }
        return $moves;
    }

    function rookMoves($r, $c, $color) {
        return $this->deltaMoves($r, $c, $color, [[-1,0],[0,1],[1,0],[0,-1]]);
    }

    function bishopMoves($r, $c, $color) {
        return $this->deltaMoves($r, $c, $color, [[-1,-1],[1,1],[-1,1],[1,-1]]);
    }

    function queenMoves($r, $c, $color) {
        return $this->deltaMoves($r, $c, $color, [[-1,0],[-1,-1],[-1,1],[0,-1],[0,1],[1,0],[1,-1],[1,1]]);
    }

    function knightMoves($r, $c, $color) {
        return $this->oneDeltaMoves($r, $c, $color, [[-2,1],[-2,-1],[2,1],[2,-1],[-1,2],[-1,-2],[1,2],[1,-2]]);
    }

    function kingMoves($r, $c, $color) {
        return $this->oneDeltaMoves($r, $c, $color, [[-1,0],[-1,-1],[-1,1],[0,-1],[0,1],[1,0],[1,-1],[1,1]]);
    }

    function deltaMoves($r, $c, $color, $deltas) {
        $moves = [];
        $opColor = $color === 'w' ? 'B': 'w';
        foreach($deltas as $delta) {
            $goOn = True;
            $curR = $r;
            $curC = $c;
            while ($goOn) {
                $curR = $curR + $delta[0];
                $curC = $curC + $delta[1];
                if ($curR < 0 || $curC < 0 || $curR > 7 || $curC > 7) {
                    $goOn = False;
                }
                else if ($this->color($curR, $curC) === $color) {
                    $goOn = False;
                }
                else if ($this->color($curR, $curC) === $opColor) {
                    array_push($moves, [$curR, $curC]);
                    $goOn = False;
                }
                else {
                    array_push($moves, [$curR, $curC]);
                }
            }
        }
        return $moves;
    }

    function oneDeltaMoves($r, $c, $color, $deltas) {
        $moves = [];
        $opColor = $color === 'w' ? 'B' : 'w';
        foreach($deltas as $delta) {
            $curR = $r + $delta[0];
            $curC = $c + $delta[1];
            if ($curR >= 0 && $curC >= 0 && $curR < 8 && $curC < 8) {
                if ($this->color($curR, $curC) !== $color) {
                    array_push($moves, [$curR, $curC]);
                }
            }
        }
        $logstr = "";
        foreach($moves as $move) {
            $logstr = "$logstr [$move[0], $move[1]]";
        }
        error_log($logstr);
        return $moves;
    }

    function moveInList($rowTo, $colTo, $moveList) {
        foreach($moveList as $move) {
            if ($rowTo === $move[0] && $colTo === $move[1]) {
                return True;
            }
        }
        error_log('notInList');
        return False;
    }

    function move($rowFrom, $colFrom, $rowTo, $colTo) {
        $piece = $this->squares[$rowFrom][$colFrom];
        $this->squares[$rowFrom][$colFrom] = '';
        $this->squares[$rowTo][$colTo] = $piece;
    }

    function victory() {
        $whiteKingCount = 0;
        $blackKingCount = 0;
        foreach ($this->squares as $row) {
            foreach ($row as $square) {
                if ($square == 'wK') {
                    $whiteKingCount += 1;
                }
                else if ($square == 'BK') {
                    $blackKingCount += 1;
                }
            }
        }
        if ($blackKingCount == 0) {
            return 'w';
        }
        else if ($whiteKingCount == 0) {
            return 'B';
        }
        return '';
    }

    function toStr() {
        $str = (string)($this->turn);
        foreach ($this->squares as $row) {
            foreach ($row as $square) {
                $str = "$str,$square";
            }
        }
        return $str;
    }

    function setFromStr($str) {
        $vals = explode(',', $str);
        $this->turn = (int)($vals[0]);
        $p = 1;
        $this->squares = [];
        for ($i = 0; $i < 8; $i++) {
            $row = [];
            for ($j = 0; $j < 8; $j++) {
                array_push($row, $vals[$p]);
                $p++;
            }
            array_push($this->squares, $row);
        }
    }

    function infoString() {
        $infstr = '';
        $first = True;
        foreach ($this->info['captures'] as $capture) {
            $infstr += "$capture,";
        }
        return $infstr;
    }

    function setInfo($str) {
    }

    function toJSON() {
        $vals = array(
            "squares" => $this->squares,
            "turn" => $this->turn,
            "victory" => $this->victory(),
            "str" => $this->toStr(),
            "info" => $this->stringinfo
        );
        return json_encode($vals);
    }

    function writeToDB() {
        global $pdo;
        $statement = $pdo->prepare('INSERT INTO positions(board) VALUES(?);');
        $statement->execute([$this->toStr()]);
    }

    function score() {
        if ($this->victory() === 'w') {
            return [999999,-999999];
        }

        if ($this->victory() === 'B') {
            return [-999999, 999999];
        }

        $scores = [
            'p' => 1.1,
            'b' => 3,
            'k' => 3.1,
            'r' => 5,
            'Q' => 9
        ];

        $whiteScore = 0;
        $blackScore = 0;

        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 8; $j++) {
                if ($this->color($i, $j) === 'w') {
                    $whiteScore += $scores[$this->piece($i, $j)];
                }
                if ($this->color($i, $j) === 'B') {
                    $blackScore += $scores[$this->piece($i, $j)];
                }
            }
        }
    }
}

$board = new ChessGame();
$sql = "SELECT board FROM positions ORDER BY pk DESC LIMIT 1";
$statement = $pdo->query($sql);
$row = $statement->fetch();

if ($row) {
    $board->setFromStr($row[0]);
}
else {
    $board->writeToDB();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo $board->toJSON();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $args = [];
    parse_str($_SERVER['QUERY_STRING'], $args);
    if ($args['rf'] === 'x') {
        $board = new ChessGame(); 
        $board->writeToDB();
    }
    if ($args['turn'] === "$board->turn") {
        $rf = (int)$args['rf'];
        $cf = (int)$args['cf'];
        $rt = (int)$args['rt'];
        $ct = (int)$args['ct'];
        if ($board->legalMove($rf, $cf, $rt, $ct)) {
            $board->move($rf, $cf, $rt, $ct);
            $board->turn++;
            $board->writeToDB();
        }
    }
    echo $board->toJSON();
    exit();
}

?>
