<?php

/**
 * functions.php
 * 
 * Implementations of functions used in AI.php.
 */

/**
 * configAI()
 *
 * Parses GET input;
 * Defines the Node class corresponding to the AI selected;
 * Defines static variables of NodeClass: $size and $maxDepth.
 */
function configAI()
{
	global $board, $size, $AI, $currentPlayer;
	
	// Execute parseInput() if not yet executed
	if (!isset($AI))
	{
		parseInput();
	}
	
	// board size
	NodeClass::$size = $size;
	
	// Difficulty
	switch ($AI)
	{
		// Easy
		case "AI1":
			NodeClass::$maxDepth = 3;
			break;
		
		// Hard
		case "AI2":
			NodeClass::$maxDepth = 6;
			break;
			
		// Expert (no depth limit - becomes Heuristic 1)
		case "AI3":
			NodeClass::$maxDepth = 0;
	}
	
	// Load node class
	require_once 'minimax.php';
	
}

/**
 * parseInput()
 * 
 * Parses input received in $_GET.
 * http://gfrd.ddns.net:26128/AI.php?n=3&currentPlayer=1&board=001+012+020+100+110+120+200+210+220&AI=AI1
 */
function parseInput()
{
	global $board, $size, $AI, $currentPlayer;
	
	// Extract values from $_GET and parse "board" string
	extract($_GET);

	// Correct current player
	if ($currentPlayer == 2)
	{
		$currentPlayer = -1;
	}

	// Build board array - split string using regular expression
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
	
	// set global variables
	$size = $n;
	$board = $brd;
	// AI already defined
	// currentPlayer already defined
}


?>