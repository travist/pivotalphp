#!/usr/bin/php
<?php

require_once('cliphp/cli.php');
require_once('includes/pivotaltracker_rest.php');
require_once('includes/tcpdf/config/lang/eng.php');
require_once('includes/tcpdf/tcpdf.php');

// To keep stupid warnings from showing up...
date_default_timezone_set('America/Chicago');

// Create the new CLI object.
$cli = new CLIPHP();

// Get all of the files in the scripts folder.
$dir = dirname(__FILE__) . "/scripts";
$files = array();
if (is_dir($dir)) {
  if ($contents = opendir($dir)) {
    while (($node = readdir($contents)) !== false) {
      if ($node != "." && $node != "..") {
        $path = $dir . DIRECTORY_SEPARATOR . $node;
        $ext = strtolower(substr($node, strrpos($node, '.') + 1));
        if ($ext == 'php' && !is_dir($path) && $node != 'storycards_html.php') {
          $files[$path] = $node;
        }
      }
    }
  }
}


/**
 * Presents a list of options to the user, and returns their choice
 * 
 * @return <type>
 */

function promptUserChoice( $prompt, $arg, $options )
{
  global $cli;

  $i = 1;
  foreach($options as $option => $o)
  {
    $prompt .= '    ' . $i . ') ' . $o . "\n";
    $i++;
  }

  $input = $cli->get($arg, $prompt);
  if (is_numeric($input) && $input <= count($options))
  {
    $keys = array_keys($options);
    return $keys[($input - 1)];
  }
  else
  {
    echo "Invalid input here.  Please try again.\n";
    return 0;
  }
}

/**
 * Get's your pivotal tracker token.
 * 
 * @return array
 */
function getToken() {
  global $cli;
  if (isset($cli->args['token'])) {
    return $cli->args['token'];
  }
  else {
    $username = $cli->get("username", "Enter your Pivotal Tracker user name. ( You will only need to do this once ):");
    $password = $cli->get("password", "Enter you Pivotal Tracker password. ( You will only need to do this once ):");
    $output = shell_exec('curl -u ' . $username . ':' . $password . ' -X GET https://www.pivotaltracker.com/services/v3/tokens/active');
    $matches = array();
    preg_match('/\<guid\>([0-9a-zA-Z]+)\<\/guid\>/', $output, $matches);
    return $matches[1];
  }
}

/**
 * Get's the PDF object.
 * 
 * @param <type> $config
 * @return TCPDF
 */
function getPDF($args) {
  // create new PDF document
  global $cli;

  //landscape for bullet_list
  if ($cli->args['script'] == "bullet_list.php") {
    $pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  }
  else {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
  }

  // set document information
  $pdf->SetCreator($args['name']);
  $pdf->SetAuthor($args['name']);
  $pdf->SetTitle($args['title']);
  $pdf->SetSubject($args['title']);

  // remove default header/footer
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);

  // set default monospaced font
  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

  //set margins
  $pdf->SetMargins(5, 5, 5);

  //set auto page breaks
  $pdf->SetAutoPageBreak(TRUE, 5);

  //set image scale factor
  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

  return $pdf;
}

/**
 * Get's the privotal tracker stories based on a filter.
 * 
 * @param <type> $config
 * @param <type> $filter
 * @return <type>
 */
function getStories($args) {
  // Now create a new PivotalTracker object.
  $pivotal = new PivotalTracker($args['token']);
  $args['filter'] = isset($args['filter']) ? 'label:"' . $args['filter'] . '"' : '';
  $args['filter'] .= 'includedone:true';
  return $pivotal->stories_get_by_filter($args['project'], $args['filter']);
 }

$cli->get("name", "Enter your full name. ( You will only need to do this once ):", TRUE);
$cli->set("token", getToken(), TRUE);
$cli->get("project", "Enter you Pivotal Tracker project ID. ( You will only need to do this once ):", TRUE);
$cli->get("title", "Title: ");
$cli->get("filter", "Filter: ");

$cli->set("script", promptUserChoice("Select an output script:\n", "script", $files));
$sortOrders = array('story_type' => 'Story type', 'estimate' => 'Estimate', 'requested_by' => 'Requested by', 'owned_by' => 'Owned by', 'current_state' => 'Current state', 'none' => 'None');
$formats = array('HTML' => 'HTML', 'PDF' => 'PDF');
$cli->set("html", promptUserChoice("Select an output format:\n", "html", $formats));
$cli->set("sortOrder", promptUserChoice("Select a sort order:\n", "sortOrder", $sortOrders));

// Make sure we have everything.
if ($cli->args['token'] && $cli->args['project'] && $cli->args['title'] && $cli->args['script']) {

  // Get the PDF.
  $pdf = getPDF($cli->args);

  // Get the stories.
  $stories = getStories($cli->args);

  if ($stories) {

    // Include the script.
    echo "Source File: " . $cli->args['script'] . "\n";
    require_once($cli->args['script']);

    // counts for stories
    //var_dump($stories);
    //exit();

    //counts for stories
    $type_cnt = array('bug'=>0,'feature'=>0,'release'=>0);
    $est_type_cnt   = array('bug'=>0,'feature'=>0,'release'=>0);
    $est_accept_type_cnt = array('bug'=>0,'feature'=>0,'release'=>0);
    $state_type_cnt = array();
    $est_cnt = 0;
    foreach ($stories as $story) {
      if ($story['story_type']) {
        $type_cnt[$story['story_type']]++;
	if ($story['estimate'] > 0) {
          $est_cnt = $est_cnt + $story['estimate'];
	  if ($story['current_state'] == 'accepted') {
   	    $est_accept_type_cnt[$story['story_type']] += $story['estimate'];
	    $est_accept_cnt += $story['estimate'];
	  }
	  $est_type_cnt[$story['story_type']] += $story['estimate'];
        }
        $state_type_cnt[$story['story_type']][$story['current_state']]++;
      }    
    }
//    var_dump($est_type_cnt);
//    var_dump($state_type_cnt);

    //var_dump($type_cnt);
    $msg = "---------------\nTOTALS:\n";
    foreach ($type_cnt as $type => $type_count) {
      $msg .= sprintf ("   %-15.15s : %4d\n",$type."(s)",$type_count);
    }
    $msg .=  "---------------\n";
    $msg .=  "estimate total:$est_cnt\n";
    $msg .=  "---------------\n";

    print $msg;

    $msg2 = sprintf ("Script Type: %s\n", $cli->args['script']);
    print $msg2;

    //Sorts the stories by the user's choice
    $sortBy = array();
    $requested_by = array();
    $sortChoice = $cli->args['sortOrder'];
    $msg3 = sprintf ("Will sort by %s\n", $sortChoice);
    print $msg3;

    foreach($stories as $key => $item)
    {
      $sortBy[$key] = $item[$sortChoice];
      $requested_by[$key] = $item['requested_by'];
    }

    //For each sort, the requester is used as the secondary sort key for more order
    //Estimate is sorted descending, so that the most important stories are first
    if ($sortChoice == 'estimate')
    {
      array_multisort($sortBy, SORT_DESC, $requested_by, SORT_ASC, $stories);
    }
    else
    {
      array_multisort($sortBy, SORT_ASC, $requested_by, SORT_ASC, $stories);
    }

    $output = '';
    //Outputs as HTML
    if ($cli->args['html'] == 'HTML')
    {
      print "Will be in HTML\n";
      $filename = $cli->args['title'] . '.html';
      if ($cli->args['script'] != 'storycards.php')
      {
        pdf_contents($pdf, $cli->args, $stories, $output);
      }
      else
      {
        require_once('scripts/storycards_html.php');
        storycard_contents($pdf, $cli->args, $stories, $output);
      }
      file_put_contents( dirname(__FILE__) . '/' . $filename, $output);
      echo "Successfully created " . $filename . "!\n";
    }

    //Outputs as a PDF
    else
    {
      print "Will be a PDF\n";
      pdf_contents($pdf, $cli->args, $stories, $output);
      $pdf->writeHTML($output);
      $pdf_output = $pdf->Output('doc.pdf', 'S');

      $filename = $cli->args['title'] . '.pdf';
      file_put_contents( dirname(__FILE__) . '/' . $filename, $pdf_output);
      echo "Successfully created " . $filename . "!\n";
    }
  }
  else {
    echo "No stories found.\n";
  }
}
else {
  echo "Invalid Input:  Please try again.\n";
}
?>
