<?php
/**
 * nodeclass2.php
 * 
 * Class for node objects, to be used in the minimax algorithm, using the
 * heuristic from https://www.ntu.edu.sg/home/ehchua/programming/java/JavaGame_TicTacToe_AI.html
 * 
 * This file implements Alpha-Beta pruning.
 * 
 */

abstract class NodeClass
{
	static $size;					// (int)	Board size. It is a static variable (belongs to nodeclass1, not to objects),
									// 			and must be initialised externally.
	static $maxDepth = 0;			// (int)	Maximum depth to be reached.
	public $board = [];				// (array)	board configuration of current node
	public $availableMoves = [];	// (string) List of available moves (blank tiles) of this node. Format: "xy",
									//			where x and y are the horizontal and vertical coordinates.
	protected $currentPlayer;		// (int)	Current player turn in this node
	protected $depth;				// (int)	Depth of this node from root node
	protected $maxPoints;			// (mixed)	Maximum number of points possible from this branch. Used in Alpha-Beta pruning.
	protected $minPoints;			// (mixed)	Minimum number of points possible from this branch.
	
	/**
	 * __construct($brd, $plr, $dpth)
	 * @param array $brd				Current board state 
	 * @param int $plr					Current player (+1 or -1)
	 * @param int $dpth					Depth - distance from root node. Default is 0 (root node).
	 */
	function __construct($brd, $plr, $dpth = 0, $max = false, $min = false)
	{
		$this->board = $brd;
		$this->currentPlayer = $plr;
		$this->depth = $dpth;
		$this->maxPoints = $max;
		$this->minPoints = $min;
		
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
		if ( (($winner = $this->winState()) === false) && (($this->depth < self::$maxDepth) || (self::$maxDepth == 0)) )
		{
			$points = false;
			foreach ($this->availableMoves as $index => $move)
			{
				// Create child node and get the its heuristic value.
				$newBoard = $this->newBoard($index);
				$child = new $className(
						$newBoard,
						-$this->currentPlayer,
						$this->depth + 1,
						$this->maxPoints,
						$this->minPoints
						);
				$childPoints = $child->evaluateNode();
				$child = null;
				
				// Set this node's maximum points
				if 	(
						(($this->currentPlayer*$childPoints) > ($this->currentPlayer*$points)) || 
						($points === false)
					)
				{
					$points = $childPoints;
				}
				
				// Alpha-Beta pruning.
				if ($this->AlphaBeta($points))
				{
					break;
				}
			}
		}
	
		// If reached max depth or leaf node, compute the points of this node
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
	 * Implemented separately, to allow for different heuristics, if desired.
	 * 
	 * Return Value:	The number of points of current node.
	 * 
	 */
	abstract protected function heuristic();
	
	/**
	 * AlphaBeta()
	 * 
	 * Implements Alpha-Beta Pruning. Checks this node's points against the Maximum (alpha) or Minimum (beta) of the branch.
	 * 
	 * Input:	$points	- Number of points this node is currently giving (maximum or minimum, depending on currentPlayer).
	 * Output:	Boolean	- True, if $points exceeds MAX or MIN, meaning evaluateNode may stop iteration.
	 * 					  False otherwise.
	 * 
	 */
	protected function AlphaBeta($points)
	{
		// GFRD: "Maximising node checks against MAX (alpha) and updates its MIN (beta).
		//		  Minimising node checks agains MIN (beta) and updates its MAX (alpha)."
		
		// Maximising player
		if ($this->currentPlayer == 1)
		{
			// Check against maxPoints - interrupt iteration if points is >= MAX
			// possible for this branch - it will never be selected.
			if ($this->maxPoints !== false)
			{
				if ($points >= $this->maxPoints)
				{
					// interrupt iteration
					return true;
				}
			}
				
			// Update MIN if necessary
			if ($this->minPoints === false)
			{
				$this->minPoints = $points;
			}
			elseif ($points > $this->minPoints)
			{
				$this->minPoints = $points;
			}
		}
		
		// Minimising player
		else
		{
			// Check against minPoints - interrupt iteration if points is <= MIN
			// possible for this branch - it will never be selected.
			if ($this->minPoints !== false)
			{
				if ($points <= $this->minPoints)
				{
					// interrupt iteration
					return true;
				}
			}
		
			// Update MAX if necessary
			if ($this->maxPoints === false)
			{
				$this->maxPoints = $points;
			}
			elseif ($points < $this->maxPoints)
			{
				$this->maxPoints = $points;
			}
		}
		
		// Return false to allow evaluateNode to continue iterating.
		return false;
		
		/* 	Final note:
		
			I have noticed that this input:
			http://tictactoe/AI.php?n=3&currentPlayer=2&board=001%20010%20020%20100%20110%20120%20200%20210%20220&AI=AI3
			(AI is minimising player - currentPlayer = -1)
			
			Equivalent to:
			
			 X |   |
			---+---+---
			   |   |
			---+---+---
			   |   |
			Gives these points:
			[ 103, 102, 103, 0, 0, 99, 0, 0 ]
			
			Comparing to the points received without Alpha-Beta pruning:
			[ 103, 102, 103, 0, 103, 102, 103, 100 ]
			
			The second result is the "true" result. It means that the only
			move that does not result in defeat is the middle tile, which is
			correct in this case.
			
			With Alpha-Beta pruning, the same result has been found up to
			the middle tile. From here, however, I obtained 3 other zeroes.
			
			This happens because of the conditions checking the heuristic 
			value against maxPoints and minPoints:
			
			if ($points >= $this->maxPoints) and if ($points <= $this->minPoints)
			
			Specifically, it happens due to the comparison operators: greater *or equal* /
			less than *or equal*.
			
			This means that, after middle tile has been found to give AT MOST 0 points, when
			a new move is found to give >= points to the opponent, the rest of the branch is
			ignored.
			Therefore, after middle tile is reached, every time a draw leaf is found to be
			the worst result to the opponent, that branch is ignored, but the 0 points remains
			registered.
			
			So the result is not wrong, but rather a consequence of using >= and <= operators.
			If we want to continue searching the tree even if a draw is found, and change the
			operators to > and <, we obtain this result:
			[ 103, 102, 103, 0, 100, 99, 100, 99 ]
			
			Note that now the algorithm continued to search even if a 0 was found, and only stopped
			when in fact a result > 0 was found. While this makes it clear once again that the
			middle tile is the only move that avoids defeat, it made the search take 2x the time
			than using >= and <= operators.
			
			Consequently, >= and <= should be used for faster search. Since this may give multiple
			"best moves", only the FIRST INDEX corresponding to the best move should be output.
			It is the first index of the MAX/MIN points that affected the rest of the search.
			
		*/
	}
	
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