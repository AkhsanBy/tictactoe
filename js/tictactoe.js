/**
 * tictactoe.js
 * Contains functions for the Tic Tac Toe game.
 */

// Global variable for board state
var board = [];
var n;
var blanks; // Number of blank tiles.

// Global variable for DOM elements that point to board.
var boardref = [];

// Global variable for current player (1 = X; -1 = O; false = not playing).
var currentPlayer = false;

// Global variable to determine human or AI players
var players = {};

// Global variable for message element
var message = document.getElementById("msg");

/**
 * Initialises board.
 * Input: size = size of board. Default = 3.
 */
function init(size = 3)
{
	// Initialise board
	n = size;
	blanks = n*n;
	let tbl = document.getElementById("board").children[0]; // get <tbody> tag
	tbl.innerHTML = ""; // remove old board
	message.innerHTML = "";
	let newRow = [];
	
	for (let i = 0; i < size; i++) {
		board[i] = [];
		boardref[i] = [];
		
		// Create new board row
		newRow = tbl.insertBefore(document.createElement("tr"), null);
		
		// Create table columns
		for (let j = 0; j < size; j++) 
		{
			// board array
			board[i][j] = 0;
			
			// board html elements
			boardref[i][j] = newRow.insertBefore(document.createElement("td"), null);
			
			// add class according to board position
			if (i == 0) {
				if (j == 0) {
					boardref[i][j].classList.add("A1"); // top left corner
				}
				else if (j == (size - 1)) {
					boardref[i][j].classList.add("C1"); // top right corner
				}
				else {
					boardref[i][j].classList.add("B1"); // top edge
				}
			}
			else if (i == (size - 1)) {
				if (j == 0) {
					boardref[i][j].classList.add("A3"); // bottom left corner
				}
				else if (j == (size - 1)) {
					boardref[i][j].classList.add("C3"); // bottom right corner
				}
				else {
					boardref[i][j].classList.add("B3"); // bottom edge
				}
			}
			else {
				if (j == 0) {
					boardref[i][j].classList.add("A2");
				}
				else if (j == (size - 1)) {
					boardref[i][j].classList.add("C2");
				}
				else {
					boardref[i][j].classList.add("B2");
				}
			}
		}
	}
	
}

function draw() {
	// Possible values for board:
	// 0 : blank
	// 1 : X
	// -1: O
	//
	// Blank tiles may be clicked; X or O may not.
	
	// Check if each board element has an image already. Remove if yes, then add
	// X, O or blank image.
	for (let i = 0; i < n; i++) {
		for (let j = 0; j < n; j++) {
			// remove existing image, if any
			boardref[i][j].innerHTML = "";
			
			// Create new image
			let type;
			switch (board[i][j]) {
				case 1:
					type = "X";
					break;
				case -1:
					type = "O";
					break;
				case 0:
					type = "blank";
					break;
			}
			img = boardref[i][j].insertBefore(createImg(type), null);
			
			// Create onclick event, if playing.
			if ((currentPlayer !== false) && (type === "blank")) {
				img.onclick = function () {
					nextTurn(i.toString() + j.toString());
				};
			}
		}
	}
}

/**
 * Checks board if a player has won.
 * Winning situations:
 * - entire row or column
 * - diagonals
 * @returns
 * 1  = X has won
 * -1 = O has won
 * 0  = draw - all tiles have been filled (blanks = 0) and there are no winners.
 * false = no one has won yet - keep playing.
 */
function win()
{
	// Winning sequences: rows, columns and diagonals
	var sequence = [];
	
	// initialise sequences - n rows, n columns and 2 diagonals
	for (let i = 0; i < (2*n + 2); i++) {
		sequence[i] = [];
	}
		
	for (let i = 0; i < n; i++) 
	{
		// diagonals
		sequence[0][i] = board[i][i];			// main diagonal
		sequence[1][i] = board[n - 1 - i][i];	// second diagonal
		
		// columns
		for (let j = 0; j < n; j++) {
			sequence[2 + i][j] = board[j][i];		// columns
			sequence[2 + n + i][j] = board[i][j];	// rows
		}
	}
	
	// Check each possible winning sequence
	var winner = 0;
	for (let i = 0; (i < (2*n + 2)) && !winner; i++) 
	{
		winner = isWinSeq(sequence[i]);
	}
	
	// Check for draw
	if (!winner) {
		if (blanks == 0) {
			winner = 0; // draw
		}
		else {
			winner = false; // continue playing
		}
	}
	
	return winner;
}

/**
 * Register player moves and check if a player has won or if the game ended in a draw.
 * 
 * @param playerMove - string with 2 characters corresponding to the coordinate of the move.
 */
function nextTurn(playerMove, AImove = false) 
{
	// Do nothing if not playing
	if (currentPlayer === false) {
		console.log("Turns are disabled. currentPlayer === false.");
		return false;
	}
	
	// Do nothing if it is AI turn, and move was not made by AI.
	if ((!AImove) && (isAITurn())) {
		return false;
	}
	
	// Check playerMove syntax
	if (playerMove.length != 2) {
		console.log("Invalid playerMove syntax. Must have exactly 2 characters.");
		console.log(playerMove);
		return false;
	}
	
	// Get coordinates
	var i = parseInt(playerMove[0]);
	var j = parseInt(playerMove[1]);
	
	// check if coordinates are valid
	if ((i < 0) || (i >= n) || (j < 0) || (j >= n)) {
		console.log("Invalid player move - one of the coordinates are out of bounds.");
		console.log(playerMove);
		return false;
	}
	
	// Check if coordinates do not correspond to an already occupied tile,
	// which should also not be allowed.
	if (board[i][j] != 0) {
		console.log("Move is an occupied tile.");
		console.log(playerMove);
		return false;
	}
	
	// register move and change player
	board[i][j] = currentPlayer;
	blanks--;
	currentPlayer *= -1;
	
	// Draw new board state
	draw();
	
	// Check winning state
	var winningState = win();
	if (winningState !== false) 
	{
		endGame(winningState); // End game
	}
	
	else {
		// Display message: player n turn.
		message.innerHTML = "Player ";
		message.innerHTML += (currentPlayer == -1 ? "2 " : "1 ") + "turn.";
		
		
		// Check if current player is AI. Call AI.php if so.
		if (isAITurn()) {
			executeAJAX('/AI.php?' + inputStringAI(), nextTurn);
		}
		
		// Otherwise continue playing. Page will continue to listen for onclick on blank tiles.
	}
}

/**
 * Ends game.
 * 
 * @param winningStates
 */
function endGame(winningState) {
	// Set currentPlayer to false - not playing anymore.
	currentPlayer = false;
	
	// Display winner
	console.log(winningState);
	switch (winningState) {
		case 1:
			message.innerHTML = "<font color=\"red\">X</font> has won!";
			break;
		case -1:
			message.innerHTML = "<font color=\"blue\">O</font> has won!";
			break;
		case 0:
			message.innerHTML = "<font color=\"black\">Draw!</font>"
	}
	
	// Re-enable form and start button
	toggleForms();
}

function startGame() 
{
	// disable start button and forms
	toggleForms();
	
	// Configure human or AI players
	// http://stackoverflow.com/questions/3869535/how-to-get-the-selected-radio-button-value-using-js
	players = {
			p1: document.querySelector('input[name = "player1"]:checked').value,
			p2: document.querySelector('input[name = "player2"]:checked').value
	};
	
	// initialise board and set X to play first. currentPlayer must be defined before calling draw().
	currentPlayer = 1;
	let boardSize = document.getElementById("boardSizeSelection").value;
	init(parseInt(boardSize));
	draw();
	
	// display message "Player 1 turn"
	message.innerHTML = "Player 1 turn."
	
	// Check if AI is first to play
	if (isAITurn()) {
		executeAJAX('/AI.php?' + inputStringAI(), nextTurn);
	}
		
}

/**
 * toggleForms()
 * Enable forms, if disabled; disable forms, if enabled.
 * Their initial state is enabled.
 */
function toggleForms() {
	// Start button
	let button = document.getElementById("startButton");
	button.disabled = !button.disabled;
	
	// Radio buttons
	for (let input of document.getElementsByTagName("input")) {
		input.disabled = !input.disabled;
	}
	
	// Board size selection
	let input = document.getElementById("boardSizeSelection");
	input.disabled = !input.disabled;
}

function createImg(type) {
	// Create image
	var img = document.createElement("img");
	img.src = "/img/" + type + ".png";
	img.height = 128;
	img.width = 128;
	
	return img;
}

/**
 * Checks array for a winning sequence. It is called by win() for each
 * possible winning sequence.
 * Logic: a winning sequence happens when they are all 1s or -1s.
 * Therefore, we must have array max = array min, and value must be != 0.
 * 
 * @param seq - an array corresponding to a possible winning sequence of the board
 * @returns
 * 1 or -1: player that made the winning sequence;
 * false: given sequence is not a winning sequence.
 */
function isWinSeq(seq)
{
	var min, max;
	max = Math.max.apply(null, seq);
	min = Math.min.apply(null, seq);
	
	if ((max == min) && (max != 0)) {
		return max;
		
	}
	else {
		return false;
	}
}

/**
 * Checks if current turn is AI turn.
 * 
 * @returns
 * true, if it is AI turn. false, if not.
 */
function isAITurn()
{
	return ((currentPlayer == 1) && ((players.p1 === "AI1") || (players.p1 === "AI2") || (players.p1 === "AI3")) ) || 
			((currentPlayer == -1) && ((players.p2 === "AI1") || (players.p2 === "AI2") || (players.p2 === "AI3")) );
}

/**
 * Outputs query string for AI.php.
 * Data to be sent:
 * "n" 				= board size
 * "currentPlayer"	= 1, if AI is X, 2 if AI is O.
 * "board" 			= current board, via space separated 3-digit integers: "111 120 132 210 220 212 ..."
 * "AI"				= Type of AI selected. Possible values: "AI1", "AI2", "AI3".
 * 
 * Digit format for board: ijp
 * i and j: board coordinates
 * p: 0 = blank tile; 1 = X; 2 = O.
 * 
 * example string:
 * http://address/AI.php?n=3&currentPlayer=1&board=001+012+020+100+110+120+200+210+220&AI=AI1
 */
function inputStringAI() {
	// n
	var str = "n=" + n.toString() + "&";
	
	// currentPlayer
	str += "currentPlayer=" + (currentPlayer == -1 ? "2&" : "1&");
	
	// board
	str += "board=";
	for (let i = 0; i < n; i++) {
		for (let j = 0; j < n; j++) {
			str += i.toString() + j.toString();
			if (board[i][j] == 0) {
				str += "0 ";
			}
			else {
				str += (board[i][j] == -1 ? "2 " : "1 ");
			}
		}
	}
	
	// remove final whitespace from board string
	// http://stackoverflow.com/questions/952924/javascript-chop-slice-trim-off-last-character-in-string
	str = str.slice(0, -1);
	
	// AI
	str += "&AI=" + (currentPlayer == 1 ? players.p1 : players.p2);

	return str;
}