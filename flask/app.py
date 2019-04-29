from flask import Flask, jsonify, request
from flask_cors import CORS

app = Flask(__name__)
_ = CORS(app)


class ChessGame:
    def __init__(self, boardstring=None):
        if not boardstring:
            self.squares = [['' for i in range(8)] for j in range(8)]
            for i in range(8):
                self.squares[1][i] = 'Bp'
                self.squares[6][i] = 'wp'
            self.squares[0][0] = self.squares[0][7] = 'Br'
            self.squares[7][0] = self.squares[7][7] = 'wr'
            self.squares[0][1] = self.squares[0][6] = 'Bk'
            self.squares[7][1] = self.squares[7][6] = 'wk'
            self.squares[0][2] = self.squares[0][5] = 'Bb'
            self.squares[7][2] = self.squares[7][5] = 'wb'
            self.squares[0][3] = 'BQ'
            self.squares[0][4] = 'BK'
            self.squares[7][3] = 'wQ'
            self.squares[7][4] = 'wK'
            self.turn_num = 0
        else:
            self.set_squares_from_string(boardstring)

    def boardstring(self):
        position_string = ','.join(','.join(row) for row in self.squares)
        return "{},{}".format(self.turn_num, position_string)

    def set_squares_from_string(self, boardstring):
        square_strings = boardstring.split(',')
        turn = int(square_strings.pop(0))
        self.squares = [[square_strings.pop(0) for i in range(8)]
                        for j in range(8)]

    def print_out(self):
        for ri, row in enumerate(self.squares):
            print('|', end='')
            for ci, col in enumerate(row):
                if col != '':
                    print('{}|'.format(col), end='')
                elif (ci + ri) % 2 == 0:
                    print('##|', end='')
                else:
                    print('  |', end='')
            print()
        print()
        print("{} turn {}".format(('White', 'Black')[self.turn_num % 2],
                                  self.turn_num // 2))

    def move(self, row_from, col_from, row_to, col_to):
        piece = self.squares[row_from][col_from]
        self.squares[row_from][col_from] = ''
        self.squares[row_to][col_to] = piece

    def victory(self):
        if sum(row.count('BK') for row in self.squares) == 0:
            return 'w'
        elif sum(row.count('wK') for row in self.squares) == 0:
            return 'B'
        else:
            return ''

game = ChessGame()

@app.route('/boardsquares', methods=['get'])
def boardsquares():
    return jsonify({'squares': game.squares, 'turn': game.turn_num, 'victory': game.victory()})

@app.route('/move/<int:row_from>/<int:col_from>/<int:row_to>/<int:col_to>', methods=['put'])
def move(row_from, col_from, row_to, col_to):
    game.move(row_from, col_from, row_to, col_to)
    game.turn_num += 1
    return jsonify({'squares': game.squares, 'turn': game.turn_num, 'victory': game.victory()})

@app.route('/reset', methods=['put'])
def reset():
    global game
    game = ChessGame()
    return jsonify({'squares': game.squares, 'turn': game.turn_num, 'victory': game.victory()})


if __name__ == "__main__":
    app.run('127.0.0.1', debug=True)
