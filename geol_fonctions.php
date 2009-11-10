<?php

// http://www.statemaster.com/encyclopedia/Geographic-coordinate-conversion
function base60($coord){
	return sprintf("%0.0f° %2.3f", floor(abs($coord)), 60*(abs($coord)-floor(abs($coord))));
} 

?>