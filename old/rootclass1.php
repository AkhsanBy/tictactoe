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
			$child = new Node($newBoard, -$this->currentPlayer, 1);
			$this->points[] = $child->evaluateNode();
			$child = null;
		}
		
		// I could call outputMove() directly from here, but I prefer calling manually,
		// outside the object.
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
		
		// multiply points by $this->currentPlayer, so that sign is inverted if currentPlayer = -1.
		// This ensures that max(points) will get the best move, not the worst.
		foreach ($this->points as &$point)
		{
			$point *= $this->currentPlayer;
		}

		// get move that gives the most amount of points
		$index = array_search(max($this->points), $this->points);
		$output = ["move" => $this->availableMoves[$index], "rootnode", $this];
		echo json_encode($output, JSON_PRETTY_PRINT);
		exit;
	}
}
?>