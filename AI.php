<?php
/**
 * AI.php
 *
 * Receives current board and outputs a JSON containing the AI move in the .move property.
 * The move is in the format "ij", which is then read by nextMove().
 *
 * Input is sent via GET:
 * "n" 				= board size
 * "currentPlayer"	= 1, if AI is X, 2 if AI is O.
 * "board" 			= current board, via space separated 3-digit integers: "111 120 132 210 220 212 ..."
 * "AI"				= Selected AI. Possible values: "AI1", "AI2", "AI3".
 * 					  
 *
 * http://address/AI.php?n=3&currentPlayer=1&board=001+012+020+100+110+120+200+210+220&AI=AI1
 *
 */

// Global variables for current player, board, board size and AI type.
$board = [];
$size = 0;
$AI = "";
$currentPlayer = 0;

// define classes and configure AI
require_once('functions.php');
require_once('nodeclass2.php');				// Alpha-Beta pruning

parseInput();
configAI();
require_once('rootclass2.php');

// Output move
$root = new Root($board, $currentPlayer);
$root->analyseMoves();
$root->outputMove();
?>