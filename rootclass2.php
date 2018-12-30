<?php
/**
 * rootclass1.php 
 * @author Guilherme Felipe Reis Duarte
 * 
 * Contains class for Root node. Since it extends the class called "Root",
 * which in turn extends the abstract class "NodeClass", this file must
 * be required after nodeclass.php (defines NodeClass) and [AI].php (defines 
 * Node).
 * 
 * Update: included a few lines to allow use of Alpha-Beta pruning from
 * Root Node.
 * 
 * 21/01/2017
 */


class Root extends Node
{
	// default value for points: empty array.
	public $points = [];
	
	/**
	 * analyseMoves()
	 * 
	 * Calculates the maximum number of points for each available move and saves
	 * the array to $this->points.
	 * Each element of $this->points corresponds to $this->availableMoves.
	 */
	function analyseMoves()
	{
		
		// For each subnode, get the maximum points and save it to the array $this->points
		foreach ($this->availableMoves as $index => $move)
		{
			$newBoard = $this->newBoard($index);
			$child = new Node(
					$newBoard, 
					-$this->currentPlayer, 
					1, 
					$this->maxPoints,
					$this->minPoints
			);
			$this->points[] = $child->evaluateNode();
			$child = null;
			
			// update root's maxPoints or minPoints
			$points = $this->currentPlayer == 1 ? max($this->points) : min($this->points);
			$this->AlphaBeta($points);
			
		}
	}
	
	/**
	 * outputMove()
	 * 
	 * Outputs the move that gives the highest score and ends AI execution.
	 */
	function outputMove()
	{
		// Execute analyseMoves() if not yet executed.
		if (empty($this->points))
		{
			$this->analyseMoves();
		}
		
		// Find *first* index of best move: max points or min points according to current player
		// First index is important. See node of AlphaBeta() in nodeclass2.php.
		$points = $this->currentPlayer == 1 ? max($this->points) : min($this->points);
		$index = array_search($points, $this->points);
		$output = ["move" => $this->availableMoves[$index], "rootnode", $this];

		echo json_encode($output, JSON_PRETTY_PRINT);
		exit;
	}
}
?>