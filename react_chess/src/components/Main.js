import React, { Component } from 'react'
import Board from './Board'
import Info from './Info'

const apiRoot = 'http://localhost:8000'

class Header extends Component {
  render () {
    return (
      <header className='Header'>
        <h1>PHP / React Chess</h1>
        <p>by Carter Adams</p>
      </header>
    )
  }
}

class Footer extends Component {
  render () {
    return (
      <footer className='Footer'>
        <p>{'&copy'}2019</p>
      </footer>
    )
  }
}

class Main extends Component {
  state = {
    squares: [],
    turn: 0,
    victory: '',
    move: [null, null, null, null],
    side: ''
  }

  sideSquares = () => {
    if (this.state.side === 'w') {
      return this.state.squares
    }
    const rows = []
    this.state.squares.map(row => {
      rows.splice(
        0,
        0,
        (() => {
          const cols = []
          row.map(col => {
            cols.splice(0, 0, col)
          })
          return cols
        })()
      )
    })
    return rows;
  }

  sideCoords = (r, c) => {
    if (this.state.side === 'B') {
      return [7 - r, 7 - c]
    } else {
      return [r,c]
    }
  }

  timer = null

  joinClick = () => {
    if (this.state.side !== 'w' && this.state.side !== 'B') {
      this.setState({
        side: this.state.turn % 2 === 0 ? 'w' : 'B'
      }, () => {
        window.sessionStorage.setItem('side', this.state.side)
        console.log(`new side ${this.state.side} for turn ${this.state.turn}`)
      });
    } else {
      this.setState({
        side: this.state.side === 'w' ? 'B' : 'w'
      }, () => { 
        window.sessionStorage.setItem('side', this.state.side)
        console.log('swap sides')
      });
    }
  }

  getColor = (row, column) => {
    const therow = this.state.squares[row]
    const value = therow[column]
    if (value.length !== 2) {
      return false
    }
    return value.substring(0, 1)
  }

  getPiece = (row, column) => {
    const therow = this.state.squares[row]
    const value = therow[column]
    if (value.length !== 2) {
      return false
    }
    return value.substring(1, 2)
  }

  moveClick = (row, col) => {
    const r = this.state.side === 'w' ? row : 7 - row
    const c = this.state.side === 'w' ? col : 7 - col
    if (this.state.move[0] === null) {
      return this.firstClick(r, c)
    } else if (this.state.move[2] === null) {
      return this.secondClick(r, c)
    }
  }

  firstClick = (row, col) => {
    if (this.getColor(row, col) === this.state.side) {
      this.setState({ move: [row, col, null, null] })
    }
  }

  secondClick = (row, col) => {
    this.setState(
      {
        move: [this.state.move[0], this.state.move[1], row, col]
      },
      () => {
        this.move(
          this.state.move[0],
          this.state.move[1],
          this.state.move[2],
          this.state.move[3]
        )
      }
    )
  }

  move = (rowFrom, colFrom, rowTo, colTo) => {
    const args = `?rf=${rowFrom}&cf=${colFrom}&rt=${rowTo}&ct=${colTo}&turn=${this.state.turn}`
    const endpoint = `${apiRoot}/chess.php${args}`
    fetch(endpoint, {
      method: 'put',
      mode: 'cors'
    })
      .then(blob => blob.json())
      .then(json => {
        this.setState({ ...json }, () => {
          this.timer = setTimeout(() => {
            this.setState({ move: [null, null, null, null] })
          }, 250)
        })
      })
  }

  restartClick = e => {
    e.preventDefault()
    this.move('x', 'x', 'x', 'x')
  }

  pingServer = () => {
    fetch(`${apiRoot}/chess.php`)
      .then(blob => blob.json())
      .then(json => {
        if (json.turn === this.state.turn) {
          this.timer = setTimeout(this.pingServer, 250)
        } else {
          this.setState(
            { ...json, move: [null, null, null, null] },
            this.pingServer
          )
        }
      })
  }

  componentDidMount () {
    const loadState = window.sessionStorage.getItem('side') === null ? '' : window.sessionStorage.getItem('side');
    console.log(`loadstate: ${loadState}, turn: ${this.state.turn}`)
    if (this.state.squares.length === 0) {
      fetch('http://localhost:8000/chess.php')
        .then(blob => blob.json())
        .then(json => {
          this.setState({ ...json, side: loadState}, ()=>{
            if (this.state.side === '') {
              this.setState({ side:
                this.state.turn % 2 === 0 ? 'w' : 'B'
              }, ()=>{
                console.log('side set from turn')
                window.sessionStorage.setItem('side', this.state.side);
              });
            }
          });
      })
    }
    else {
      console('cDM, squares exist')
      if (loadState !== 'w' && loadState !== 'B') {
        this.setState({'side':
          this.state.turn % 2 === 0 ? 'w' : 'B'
        }, () => {
          console.log('side set from turn')
          window.sessionStorage.setItem('side', this.state.side)
        });
      }
      else {
        console.log('side set from loadState')
        this.setState({'side': loadState})
      };
    }
  }

  render () {
    if (this.timer === null) {
      this.pingServer()
    }
    return (
      <main className={`Main ${this.state.side}`}>
        <Info parent={this} />
        {<Board parent={this} />}
        <div class='Restart'>
          <button onClick={this.restartClick}>Restart Game</button>
        </div>
      </main>
    )
  }
}

export { Main, Header, Footer }
