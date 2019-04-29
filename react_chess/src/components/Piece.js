import React from 'react'
import './Piece.css'

function pieceClass(string) {
  if (string.length !== 2) {
    return 'none'
  }
  else {
    return string[0] === 'w' ? 'white' : 'black'
  }
}

const syms = {
    'p': '\u265f',
    'r': '\u265c',
    'k': '\u265e',
    'b': '\u265d',
    'Q': '\u265b',
    'K': '\u265a'
  }

function pieceSymbol(string) {
  return syms[string[1]]
}

function Piece(props) {
  return <span className={`Piece ${pieceClass(props.string)}`}>
    {pieceSymbol(props.string)}
  </span>
}

export default Piece;
