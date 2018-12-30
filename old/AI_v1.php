<?php
/**
 * AI_v1.php
 * 
 * Receives current board and outputs a JSON containing the AI move in the .move property.
 * The move is in the format "ij", which is then read by nextMove().
 * 
 * Input is send via GET:
 * "n" = board size
 * "currentPlayer"
 * "board" = current board, via space separated 3-digit integers: "111 120 132 210 220 212 ..."
 * 
 * http://gfrd.ddns.net:26128/AI.php?n=3&currentPlayer=1&board=001+012+020+100+110+120+200+210+220
 * 
 * Uses minimax algorithm described in the CS50 lectures.
 * 
 * Update 1: upon checking each possible move, stop checking tree of possibilities if a "victory" move is found.
 * (Let's see if this speeds up)
 */

// Define a class for root node
class Root
{
	public $children = [];			// Children nodes
	public $parent = [];
	public $board = [];			// Board state in this node
	static $n;						// Board size, as a static variable.
	protected $currentPlayer;		// In this node, who's turn it is.
	public $blanks = 0;
	public $win;					// 1, 0 or -1 if this is an ending node; false if not.
	public $possibleMoves = [];		// Array of possible moves from this node.
	
	function __construct($brd, $size, $plr)
	{
		// configure root node
		$this->board = $brd;
		self::$n = $size;
		
		// find blank tiles
		foreach ($this->board as $i => &$values) {
			foreach ($values as $j => &$value) 
			{
				// Save blank tiles
				if ($value == 0) 
				{
					$this->blanks++;
					$this->possibleMoves[] = strval($i) . strval($j);
				}
				// Invert the sign of board value. Root node will assume currentPlayer = 1.
				else $value *= $plr;
			}
		}
		
		// if this is first move, randomize first move
		
		if ($this->blanks == (Root::$n)*(Root::$n))
		{
			$str = (string)(rand(0, Root::$n - 1)) . (string)(rand(0, Root::$n - 1));
			echo json_encode(["move" => $str]);
			exit;
		}
		
		$this->currentPlayer = 1;
		$this->win = self::winState($this);
		
		// If reached a win state, leave possible moves empty
		if ($this->win !== false)
		{
			$this->possibleMoves = [];
		}
		
		// Otherwise, build tree of possible moves
		foreach ($this->possibleMoves as $move)
		{
			$this->children[] = new Node($this, $move, $this->blanks - 1);
		}
		
	}
	
	// outputs next move in JSON format.
	// Criteria:
	// 1. Finds moves with +1 maxPoints and randomly picks one;
	// 2. If no moves with maxpoints = +1 are found, find moves with maxpoints = 0;
	//    2.1 Pick the move that has the most children with +1 maxpoints.
	// 3. If no +0 maxpoints moves are found, pick a random move.
	function outputMove()
	{
		$this->analyseMoves();

		// Criterium 1:
		if (!empty($this->plusOne))
		{
			$moveIndex = $this->plusOne[rand(0, count($this->plusOne) - 1)];
		}
		
		// Criterium 2:
		elseif (!empty($this->zero))
		{	
			// check each children for most +1 maxponts
			$maxcount = 0;
			$moveIndex = $this->zero[0];
			
			foreach ($this->zero as $index)
			{
				$child = $this->children[$index];
				$child->analyseMoves();
				$thiscount = count($child->plusOne);
				if ($thiscount > $maxcount)
				{
					$moveIndex = $index;
				}
				
			}
		}
		
		// Criterium 3: guaranteed lose
		else 
		{
			$moveIndex = rand(0, count($this->possibleMoves));
		}
		
		// Output JSON and finish AI execution
		$output = ["move" => $this->possibleMoves[$moveIndex]];
		echo json_encode($output, JSON_PRETTY_PRINT);
		exit;
	}
	
	// Find moves that give +1, 0 or -1 as final result.
	public $plusOne = [];
	public $zero = [];
	public $minusOne = [];
	function analyseMoves()
	{
		foreach ($this->children as $i => $child)
		{
			switch ($child->maxPoints)
			{
				case 1:
					$this->plusOne[] = $i;
					break;
				case -1:
					$this->minusOne[] = $i;
					break;
				case 0:
					$this->zero[] = $i;
			}
		}
	}
	
	// Searches for winning sequences. Similar algorithm to win() of tictactoe.js
	static function winState($node)
	{
		// initialise sequences
		for ($i = 0; $i < 2*(self::$n) + 2; $i++)
		{
			$sequences[$i] = [];
		}
	
		// get possible winning sequences
		for ($i = 0; $i < (Root::$n); $i++)
		{
			// diagonals
			$sequences[0][$i] = $node->board[$i][$i];
			$sequences[1][$i] = $node->board[self::$n - $i - 1][$i];
	
			// rows and columns
			for ($j = 0; $j < (self::$n); $j++)
			{
				$sequences[2 + $i][$j] = $node->board[$i][$j];
				$sequences[2 + $i + self::$n][$j] = $node->board[$j][$i];
			}
		}
	
		// Check each possible winning sequence
		$winner = 0;
		foreach ($sequences as $seq)
		{
			$max = max($seq);
			$min = min($seq);
			if (($max == $min) && ($max != 0))
			{
				$winner = $max;
				break;
			}
		}
	
		// Check for draw
		if (($winner == 0) && ($node->blanks != 0))
		{
			$winner = false;
		}
	
		return $winner;
	}
	
}

class Node extends Root
{
	public $maxPoints; // only needed on subnodes, to decide next move. Saves best outcome
	
	// subnodes use a different construct function
	function __construct($parent, $move)
	{	
		// update board. Using "for" commands, in order to create a copy of the board.
		$this->board = [];
		for ($i = 0; $i < Root::$n; $i++)
		{
			$board[$i] = [];
			for ($j = 0; $j < Root::$n; $j++)
			{
				$this->board[$i][$j] = $parent->board[$i][$j];
			}
		}
		
		// Update board
		$i = intval($move[0]); $j = intval($move[1]);
		$this->board[$i][$j] = $parent->currentPlayer;
		$this->countBlanks();
		
		// change current player
		$this->currentPlayer = -1 * $parent->currentPlayer;
		
		// Check if it is a leaf node
		$this->win = Root::winState($this);
		if ($this->win !== false)
		{
			$this->maxPoints = $this->win;
		}
		
		// Otherwise, build tree and find maxpoints
		else 
		{
			$this->maxPoints = [];
			$this->buildPossibleMoves();
			foreach ($this->possibleMoves as $move)
			{
				$this->children = new Node($this, $move);			// create node...
				$childMaxPoints = $this->children->maxPoints;		// ... get its maximum points...
				$this->children = [];								// ... then delete subnode after reading it to free memory.
				$this->maxPoints[] = $childMaxPoints;				// has to be an array to avoid errors with min() or max().
				
				// exit loop if a "victory" is found - no need to loop more if player +1 found a move that gives +1,
				// or if player -1 found a move that gives -1.
				if ($childMaxPoints == $this->currentPlayer)
				{
					break;
				}
			}
			
			// currentPlayer = 1: me. currentPlayer = -1: opponent.
			if (($this->currentPlayer == 1))
			{
				$this->maxPoints = max($this->maxPoints);
			}
			else
			{
				$this->maxPoints = min($this->maxPoints);
			}
		}
		
	}
	
	// count blank tiles for subnodes
	function countBlanks()
	{
		$this->blanks = 0;
		foreach ($this->board as $row)
		{
			foreach ($row as $value)
			{
				if ($value == 0)
				{
					$this->blanks++;
				}
			}
		}
	}
	
	// build array of possible moves for subnodes
	function buildPossibleMoves()
	{
		// empty array of possible moves, to avoid duplicate values
		$this->possibleMoves = [];
		foreach ($this->board as $i => &$rows)
		{
			foreach ($rows as $j => &$value)
			{
				if ($value == 0) 
					$this->possibleMoves[] = strval($i) . strval($j);
			}
		}
	}
}

// Extract values from $_GET and parse "board" string
extract($_GET);

// Correct current player
if ($currentPlayer == 2)
	$currentPlayer = -1;

// split string using regular expression
// http://stackoverflow.com/questions/1792950/explode-string-by-one-or-more-spaces-or-tabs
$board = preg_split('/ +/', urldecode($board));
$brd = [];

foreach ($board as $move)
{
	$i = intval($move[0]);
	$j = intval($move[1]);
	$plr = intval($move[2]);
	if ($plr == 2) $plr = -1;
	$brd[$i][$j] = $plr;
}



// Build tree of possible moves
$root = new Root($brd, $n, $currentPlayer);

$root->outputMove();

?>