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
        if ($ext == 'php' && !is_dir($path)) {
          $files[$node] = $path;
        }
      }
    }
  }
}

/**
 * Get's which script to run.
 * 
 * @return <type>
 */
function getScript() {
  global $cli, $files;
  $i = 1;
  $output = "Select an output script:\n";
  foreach ($files as $file => $path) {
    $output .= '    ' . $i . ') ' . $file . "\n";
    $i++;
  }
  $input = $cli->get("script", $output);
  if (is_numeric($input) && $input <= count($files)) {
    $keys = array_keys($files);
    return $keys[($input - 1)];
  }
  else {
    echo "Invalid input.  Please try again.\n";
    return 0;
  }
}

/*
 * Prompts for something silently
 * Should work on UNIX/DOS
 * 
 * http://blogs.sitepoint.com/interactive-cli-password-prompt-in-php/
 */
function prompt_silent($prompt = "Enter Password:") {
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents(
      $vbscript, 'wscript.echo(InputBox("'
      . addslashes($prompt)
      . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  } else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      trigger_error("Can't invoke bash");
      return;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \""
      . addslashes($prompt)
      . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
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
    $password = prompt_silent("Enter you Pivotal Tracker password. ( You will only need to do this once ):");
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
  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
  $filter = isset($filter) ? 'label:"' . $args['filter'] . '"' : '';
  $filter .= 'includedone:true';
  return $pivotal->stories_get_by_filter($args['project'], $args['filter']);
}

$cli->get("name", "Enter your full name. ( You will only need to do this once ):", TRUE);
$cli->set("token", getToken(), TRUE);

//CODE MODIFIED - Rowdy
if(isset($cli->args['project1']))
{
    echo "Select from the following list (1,2,etc), or type a new Project ID\n";
    $temp = 1;
    while(isset($cli->args['project'.$temp]))
    {
        echo "  ".$temp.". ".$cli->args['title'.$temp].' - '.$cli->args['project'.$temp]."\n";
        $temp++;
    }
    $cli->get('project', 'Selection: ');
    if(isset($cli->args['project'.$cli->args['project']]))
    {
        $cli->set('title', $cli->args['title'.$cli->args['project']]);
        $cli->set('project',$cli->args['project'.$cli->args['project']]);
    }
    else
    {
        $cli->get('title'.$temp, "Title: ", TRUE);
        $cli->set('title', $cli->args['title'.$temp]);
        $cli->set('project'.$temp, $cli->args['project'], TRUE);
    }
}
else
{
    $cli->get("project1", "No Pivotal Tracker Project ID found\nEnter your PT PID:", TRUE);
    $cli->set("project", $cli->args['project1']);
    $cli->get("title1", "Title: ",TRUE);
    $cli->set("title", $cli->args['title1']);
}

$cli->get("filter", "Filter: ");
$cli->set("filter", str_replace(" ","%20",$cli->args['filter']));
$cli->set("script", getScript());

// Make sure we have everything.
if ($cli->args['token'] && $cli->args['project'] && $cli->args['title'] && $cli->args['script']) {

  // Get the PDF.
  $pdf = getPDF($cli->args);

  // Get the stories.
  $stories = getStories($cli->args);

  if ($stories) {

    // Include the script.
    require_once($files[$cli->args['script']]);

    // Get the output from our script.
    pdf_contents($pdf, $cli->args, $stories);

    //Close and output PDF document
    $pdf_output = $pdf->Output('doc.pdf', 'S');

    // Now write the contents to a file.
    $filename = $cli->args['title'] . '.pdf';
    file_put_contents( dirname(__FILE__) . '/' . $filename, $pdf_output);
    echo "Successfully created " . $filename . "!\n";
  }
  else {
    echo "No stories found.\n";
  }
}
else {
  echo "Invalid Input:  Please try again.\n";
}
?>
