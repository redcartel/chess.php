import React, {Component} from 'react';
import './Info.css';

class Info extends Component {
  render() {
    
    const turn = this.props.parent.state.turn % 2 === 0 ? 'w' : 'B';
    const turnWord = {'w':'White', 'B':'Black'}[turn];
    const turnPhrase = `It is ${turnWord}'s turn.`
    
    const player = this.props.parent.state.side;
    let playerSidePhrase;
    if (player === 'w') {
      playerSidePhrase = "You are playing White."
    }
    else if (player === 'B') {
      playerSidePhrase = "You are playing Black."
    }
    else {
      playerSidePhrase = "You have not joined the game.";
    }
    
    const joinButton = (
      <div className="JoinButon">
        <button onClick={this.props.parent.joinClick}>
          { player === null ? "Join game" : "Switch Sides" }
        </button>
      </div>
    );
    
    return (
      <div className={`Info ${turnWord}`}>
        <p className="TurnInfo">Turn #{this.props.parent.state.turn}. {turnPhrase}</p>
        <p className="PlayerSideInfo">{playerSidePhrase}</p>
        {joinButton}
      </div>
    )
  }
}


export default Info;
