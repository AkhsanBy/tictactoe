<?php
/**
 * nodeclass1.php
 * 
 * Class for node objects, to be used in the minimax algorithm, using the
 * heuristic from https://www.ntu.edu.sg/home/ehchua/programming/java/JavaGame_TicTacToe_AI.html
 * 
 * This file does not yet implement Alpha-Beta pruning.
 * 
 */

abstract class NodeClass
{
	static $size;				// (int)	Board size. It is a static variable (belongs to nodeclass1, not to objects),
								// 			and must be initialised externally.
	static $maxDepth = 0;		// (int)	Maximum depth to be reached.
	public $board = [];			// (array)	board configuration of current node
	public $availableMoves = [];// (string) List of available moves (blank tiles) of this node. Format: "xy",
								//			where x and y are the horizontal and vertical coordinates.
	protected $currentPlayer;	// (int)	current player turn in this node
	protected $depth;			// (int)	depth of this node from root node
	
	/**
	 * __construct($brd, $plr, $dpth)
	 * @param array $brd				Current board state 
	 * @param int $plr					Current player (+1 or -1)
	 * @param int $dpth					Depth - distance from root node. Default is 0 (root node).
	 */
	function __construct($brd, $plr, $dpth = 0)
	{
		$this->board = $brd;
		$this->currentPlayer = $plr;
		$this->depth = $dpth;
		
		// build list of available moves
		for ($i = 0; $i < self::$size; $i++)
		{
			for ($j = 0; $j < self::$size; $j++)
			{
				if ($this->board[$i][$j] == 0)
				{
					$this->availableMoves[] = strval($i) . strval($j);
				}
			}
		}
	}

	//abstract function evaluateNode();
	/**
	 * evaluateNode()
	 * 
	 * Evaluates all subnodes up to maxDepth. Returns the maximum number of points
	 * possible from this node.
	 * 
	 * To speed up processing, this function also implements the Alpha-Beta principle
	 * described in the CS50 lectures.
	 */
	public function evaluateNode()
	{
		// get class name for late static binding.
		// http://uk.php.net/lsb
		// http://stackoverflow.com/questions/1060137/instantiating-child-classes-from-parent-class-php
		$className = get_called_class();
		
		// If max depth not reached yet, evaluate subnodes (if any)
		if ( ($this->winState() === false) && (($this->depth < self::$maxDepth) || (self::$maxDepth == 0)) )
		{
			$points = [];
			foreach ($this->availableMoves as $index => $move)
			{
				$newBoard = $this->newBoard($index);
				$child = new $className($newBoard, -$this->currentPlayer, $this->depth + 1);
				$points[] = $child->evaluateNode() * $this->currentPlayer;				// invert points, if necessary
				$child = null;
			}
				
			// find greatest number of points
			$points = max($points) * $this->currentPlayer;					// invert sign again.
		}
	
		// If reached max depth, compute the points of this node
		else
		{
			$points = $this->heuristic();
		}
	
		return $points;
	}
	
	/**
	 * heuristic()
	 *
	 * This is the heuristic function that evaluates calculates how many points this node will yield.
	 * Implemented separately, to allow other possibilities, if desired.
	 * 
	 * Return Value:	The number of points of current node.
	 * 
	 */
	abstract protected function heuristic();
	
	/**
	 * newBoard(move)
	 * 
	 * Returns a new board array after playing the given move.
	 * A copy of $this->board is made, so that the original board is left unchanged.
	 * 
	 * Input:	index	int			Index of $this->availableMoves to be played.
	 * 								This ensures move will always be valid.
	 * 
	 * Output:			array		An array size x size that corresponds to the board after
	 * 								playing 'move'.
	 * 								false, if $this->availableMoves array does not have the
	 * 								given index. 
	 */
	protected function newBoard($index)
	{
		// Check if index exists
		if (!array_key_exists($index, $this->availableMoves))
		{
			return false;
		}
		
		// get coordinates
		$x = intval($this->availableMoves[$index][0]);
		$y = intval($this->availableMoves[$index][1]);
		
		// Copy current board and add new move
		$newBoard = [];
		for ($i = 0; $i < self::$size; $i++)
		{
			for ($j = 0; $j < self::$size; $j++)
			{
				$newBoard[$i][$j] = $this->board[$i][$j];
			}
		}
		$newBoard[$x][$y] = $this->currentPlayer;
		
		return $newBoard;
	}
	
	/**
	 * winState()
	 * 
	 * Checks if current node is a leaf node, i.e. if it is a Win, a Draw or a Loss.
	 * Returns +1, -1 or 0, if X wins, O wins or node is a draw, respectively.
	 * Returns false, if node is not leaf node, i.e., game is not over yet.
	 * 
	 */ 
	protected function winState()
	{
		// initialise sequences
		for ($i = 0; $i < 2*(self::$size) + 2; $i++)
		{
			$sequences[$i] = [];
		}
	
		// get possible winning sequences
		for ($i = 0; $i < (self::$size); $i++)
		{
			// diagonals
			$sequences[0][$i] = $this->board[$i][$i];
			$sequences[1][$i] = $this->board[self::$size - $i - 1][$i];
	
			// rows and columns
			for ($j = 0; $j < (self::$size); $j++)
			{
				$sequences[2 + $i][$j] = $this->board[$i][$j];
				$sequences[2 + $i + self::$size][$j] = $this->board[$j][$i];
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
		if ( ($winner == 0) && (!empty($this->availableMoves)) )
		{
			$winner = false;
		}
	
		return $winner;
	}
}
?>