<?php
class devStoryList {
	public $devName; // name of dev who owns the story
	public $storyList; // array containing story
	public $stateList; // array with states of stories
	// create new dev
	function devStoryList($devName, $storyToAdd, $state) {
		$this->devName = $devName;
		$this->storyList = array($storyToAdd); // start array off with first story
		$this->stateList = array($state); // start array off with first story's state
		return;
		//array_push($this->$storyList, $storyToAdd); // push storyToAdd onto the list
		//array_push($this->$stateList, $state); // push state to stateList
	}
	// add story to existing dev
	function addStory ($story, $state){
		array_push($this->storyList, $story);
		array_push($this->stateList, $state);
	}
}
// creates a flat text file with the story, the dev on the story, and
function pdf_contents(&$pdf, $args, $stories) {
	$devList; // array of devStoryList objects
	
	//set font
	$pdf->SetFont('courier', 11);
	
	//add a page
	$pdf->AddPage();
	
	$output = '<html><body>';
	
	// populate $devList with $stories
	$firstIteration = true;
	foreach ($stories as $story) {
		// in case story has no owner yet, give default name
		if($story['owned_by'] == NULL) {
			$story['owned_by'] = "Stories with no dev.";
		}
		// create new devStoryList
		$dev = new devStoryList($story['owned_by'], $story['name'], $story['current_state']);
		// check $devList to see if dev is already in list
		// first story will always be inserted, hence the do...while()
		$j = 0;
		do {
			//first time through list
			if($firstIteration == true) {
				$devList = array($dev);
				$firstIteration = false;
				break;
			}
			// story's dev already in the list
			else if($dev->devName == $devList[$j]->devName) {
				$devList[$j]->addStory($story['name'], $story['current_state']); // add story/state to the dev's list
				break;
			}
			// if reached end of $devList, add new devStoryList
			else if ($j == sizeof($devList)-1) {
				array_push($devList, $dev);
				break;
			}
			else // not found yet, continue on
			{
				$j++;
			}
		} while($j<sizeof($devList));
	}
	// write devList to a text file
	for($i=0; $i<sizeof($devList); $i++) {
		// write dev name
		$output.=($i+1).') '.$devList[$i]->devName.'<br>';
		// write each of the dev's stories
		for($j=0; $j<sizeof(($devList[$i]->storyList)); $j++) {
			$output.='     '.($j+1).'. '.$devList[$i]->stateList[$j].'->'.($devList[$i]->storyList[$j]).'<br>'; // write the story
		}
	}
	
	// close body
	$output .= '</body></html>';
	$pdf->writeHTML($output);
	
}
?>
