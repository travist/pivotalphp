<?php

require_once('includes/pivotaltracker_rest.php');
require_once('includes/tcpdf/config/lang/eng.php');
require_once('includes/tcpdf/tcpdf.php');
require_once('includes/helper.inc');

set_time_limit(0);
chdir(dirname(__FILE__));

// Get's the users full name.
function getName() {
  print "Enter your full name. ( You will only need to do this once ):\n";
  return readline();
}

// Gets the users pivotal tracker name.
function getUserName() {
  print "Enter your Pivotal Tracker user name. ( You will only need to do this once ):\n";
  return readline();
}

// Get the users password.
function getPassword() {
  print "Enter you Pivotal Tracker password. ( You will only need to do this once ):\n";
  return readline();
}

// Gets the users project number.
function getProject() {
  print "Enter you Pivotal Tracker project ID. ( You will only need to do this once ):\n";
  return readline();
}

// Gets the title.
function getTitle() {
  print "Title:  ";
  return readline();
}

// Gets the filter.
function getFilter() {
  print "Filter:  ";
  return readline();
}

// Gets the script to run.
function getScript() {
  $retval = array();
  print "Select an output script:\n";
  $retval['files'] = get_files("scripts", "*.php");     // Find all PHP files.
  for ($i = 0; $i < count($retval['files']); $i++) {
    print '    ' . ($i + 1) . ') ' . basename($retval['files'][$i]);
    print "\n";
  }
  $retval['selection'] = readline();
  return $retval;
}

function getConfig() {
  $config = array();
  $handle = fopen('config', 'a+');
  if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
      $values = explode('=', $buffer);
      $config[trim($values[0])] = trim($values[1]);
    }
    if (!feof($handle)) {
      echo "Error: unexpected fgets() fail\n";
    }

    if( !isset($config['name']) ) {
      $config['name'] = getName();
      fwrite($handle, 'name=' . $config['name'] . "\n");
    }

    if( !isset($config['username']) ) {
      $config['username'] = getUserName();
      fwrite($handle, 'username=' . $config['username'] . "\n");
    }

    if( !isset($config['password']) ) {
      $config['password'] = getPassword();
      fwrite($handle, 'password=' . $config['password'] . "\n");
    }

    if( !isset($config['project']) ) {
      $config['project'] = getProject();
      fwrite($handle, 'project=' . $config['project'] . "\n");
    }

    fclose($handle);
  }

  return $config;
}

function getPDF( $config, $title ) {
  // create new PDF document
  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

  // set document information
  $pdf->SetCreator($config['name']);
  $pdf->SetAuthor($config['name']);
  $pdf->SetTitle($title);
  $pdf->SetSubject($title);

  // remove default header/footer
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);

  // set default monospaced font
  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

  //set margins
  $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

  //set auto page breaks
  $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

  //set image scale factor
  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

  return $pdf;
}

function getStories( $config, $filter ) {
  // Now create a new PivotalTracker object.
  $pivotal = new PivotalTracker(null, $config['username'], $config['password']);
  $filter = isset($filter) ? 'label:"' . $filter . '"' : '';
  $filter .= 'includedone:true';
  return $pivotal->stories_get_by_filter($config['project'], $filter);
}

$config = getConfig();
$title = getTitle();
$filter = getFilter();
$script = getScript();

// Make sure we have everything.
if ( $config['username'] && $config['password'] && $title && $script['selection']) {

  // Convert their selection to an integer.
  settype($script['selection'], "integer");

  // Check the number.
  if (is_integer($script['selection']) && $script['selection'] <= count($script['files'])) {
    // Get the PDF.
    $pdf = getPDF( $config, $title );

    // Get the stories.
    $stories = getStories( $config, $filter );

    // Include the script.
    require_once($script['files'][($script['selection'] - 1)]);

    // Get the output from our script.
    $output = getOutput( $pdf, $title, $stories );

    // Now add the output to the PDF.
    $pdf->writeHTML($output);

    //Close and output PDF document
    $pdf_output = $pdf->Output('doc.pdf', 'S');

    // Now write the contents to a file.
    file_put_contents( $title . '.pdf', $pdf_output );
  }
  else {
    echo "\nInvalid Selection.  Please Select a Number.";
  }
}
?>
