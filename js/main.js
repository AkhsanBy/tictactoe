// Initialise global variables and draw empty board after page has loaded
// http://stackoverflow.com/questions/588040/window-onload-vs-document-onload
window.onload = function () {
	init();
	draw();
}
document.getElementById("startButton").onclick = startGame;