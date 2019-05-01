import React, { Component } from 'react'
import Piece from './Piece'
import './Board.css'

class Board extends Component {

  rows (squares) {
    return this.props.parent.sideSquares().map((row, i) => <tr key={i}> {this.rowTDs(row, i)}</tr>)
  }

  pHighlight(row,col) {
    const move = this.props.parent.state.move
    if (move[0] === null) {
      return false;
    }
    const fsquare = this.props.parent.sideCoords(move[0], move[1]);
    if (row === fsquare[0] && col === fsquare[1]) {
      return true;
    }
    if (move[2] === null) {
      return false;
    }
    const tsquare = this.props.parent.sideCoords(move[2], move[3]);
    if (row === tsquare[0] && col === tsquare[1]) {
      return true;
    }
    return false;
  }

  rowTDs (row, rownum) {
    return row.map((col, colnum) => {
      const move = this.props.parent.state.move
      const squareColor = (colnum + rownum) % 2 ? 'black' : 'white'
      const highlight = this.pHighlight(rownum, colnum) ? 'highlight' : ''
      const className = squareColor + ' ' + highlight
      
      return (
      <td
        className = {className}
        key={colnum}
        onClick={() => {
          this.props.parent.moveClick(rownum, colnum)
        }}
      >
        <Piece string={col} />
      </td>
      )}
    )
  }

  render () {
    console.log('Board', this.props.parent.state)
    return (
      <div className='Board'>
        <div className='spacer'></div>
        <table className='BoardDisplay'>
          {this.rows(this.props.parent.state.squares)}
        </table>
        <div className='spacer'></div>
      <div class='Restart'>
  <button onClick={this.props.parent.restartClick}>Restart Game</button>
</div>
<div className='spacer'></div>
      </div>
    )
  }
}

export default Board
