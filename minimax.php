<?php
/**
 * minimax.php
 * 
 * Implements the Minimax algorithm using Heuristic 2:
 * https://www.ntu.edu.sg/home/ehchua/programming/java/JavaGame_TicTacToe_AI.html
 *  - +1000 for EACH 4-in-a-line for current player (size = 4);
 *  - +100 for EACH 3-in-a-line for current player;
 *  - +10  for EACH 2-in-a-line and empty cells for current player;
 *  - +1   for EACH 1-in-a-line and empty cells for current player;
 *  - negative scores, if it is opponent's turn;
 *  - 0 otherwise.
 *  
 *  In addition, includes the alpha-beta principle described in the CS50x 2015
 *  lecture, week 11.
 */

class Node extends NodeClass
{
	/**
	 * 
	 * {@inheritDoc}
	 * @see NodeClass::evaluateNode()
	 */

	/**
	 * heuristic()
	 * 
	 * Implements heuristic. Returns the sum of all computed points. Obtained from:
	 * https://www.ntu.edu.sg/home/ehchua/programming/java/JavaGame_TicTacToe_AI.html
	 * 
	 *  - +100 for EACH 3-in-a-line for current player;
	 *  - +10  for EACH 2-in-a-line and one empty cell for current player;
	 *  - +1   for EACH 1-in-a-line and 2 empty cells for current player;
	 *  - negative scores, if it is opponent's turn;
	 *  - 0 otherwise.
	 */
	protected function heuristic()
	{
		$total = 0;
		$diagonal1 = [];
		$diagonal2 = [];
		$total = 0;
		
		// Iterate through board
		for ($i = 0; $i < self::$size; $i++)
		{
			$row = [];
			$column = [];
			
			// Form Rows and Columns and compute their points
			for ($j = 0; $j < self::$size; $j++)
			{
				$row[] 		= $this->board[$i][$j];
				$column[] 	= $this->board[$j][$i];
			}
			$total += self::pointsForRow($row);
			$total += self::pointsForRow($column);
			
			// Form diagonals
			$diagonal1[] = $this->board[$i][$i];
			$diagonal2[] = $this->board[self::$size - $i - 1][$i];
		}
		
		// Compute points given by diagonals
		$total += self::pointsForRow($diagonal1);
		$total += self::pointsForRow($diagonal2);
		
		// Return the sum of all points
		return $total;
	}
	
	/**
	 * pointsForRow($row)
	 * 
	 * Calculates how many points the given row gives. "Row" here means a row, a column or a diagonal
	 * of the board.
	 * 
	 * @param array $row	A "row" of the board. Must contain exactly self::$size elements.$this
	 * 
	 * @return				The number of points found, according to the Heuristic.
	 * 						If $row is invalid, returns false.
	 */
	private static function pointsForRow($row)
	{
		// Check the number of elements of $row
		if (count($row) != self::$size)
		{
			return false;
		}
		
		// Iterate through elements
		$foundP1 = false;
		$foundP2 = false;
		$partial = 0;
		
		for ($i = 0; $i < self::$size; $i++)
		{
			// found a +1 or -1 move. Set $partial to +1 or -1 if it was 0 (first non-empty tile found).
			if ($row[$i] == 1)
			{
				$foundP1 = true;
				if ($partial == 0)
				{
					$partial = 1;
					continue;					// continue loop to avoid multiplying it by 10 with a single X.
				}
			}
		
			if ($row[$i] == -1)
			{
				$foundP2 = true;
				if ($partial == 0)
				{
					$partial = -1;
					continue;					// continue loop to avoid multiplying it by 10 with a single O.
				}
			}
		
			// Found an X and O in the same row: 0 points. End loop.
			if ($foundP1 && $foundP2)
			{
				$partial = 0;
				break;
			}
		
			// Only X or only O: sum points
			elseif (($foundP1 xor $foundP2) && ($row[$i] != 0))
			{
				$partial *= 10 * $row[$i];
			}
			
			// Do nothing if both $foundP1 and $foundP2 are false.
		}
		return $partial;
	}
}

?>