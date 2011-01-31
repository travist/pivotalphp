<?php

require_once('includes/pivotaltracker_rest.php');
require_once('includes/tcpdf/config/lang/eng.php');
require_once('includes/tcpdf/tcpdf.php');
require_once('includes/helper.inc');

set_time_limit(0);
chdir(dirname(__FILE__));

// The current argument index.
$arg = 1;

/**
 * Get the next argument.
 */
function getArgument( $text ) {
  global $argv, $arg;
  if( isset($argv[$arg]) ) {
    return $argv[$arg++];
  }
  else {
    print $text . "\n";
    return readline();
  }
}

/**
 * Get's which script to run.
 * 
 * @return <type>
 */
function getScript() {
  $retval = array();
  $retval['files'] = get_files("scripts", "*.php");     // Find all PHP files.

  $output = "Select an output script:\n";

  for ($i = 0; $i < count($retval['files']); $i++) {
    $output .= '    ' . ($i + 1) . ') ' . basename($retval['files'][$i]) . "\n";
  }

  $selection = getArgument($output);
  if(!is_numeric($selection)) {
    for ($i = 0; $i < count($retval['files']); $i++) {
      if ($arg == basename($retval['files'][$i])) {
        $selection = ($i + 1);
        break;
      }
    }
  }

  $retval['selection'] = $selection;
  return $retval;
}

/**
 * Get's your pivotal tracker token.
 * 
 * @return array
 */
function getToken() {
  $username = getArgument("Enter your Pivotal Tracker user name. ( You will only need to do this once ):");
  $password = getArgument("Enter you Pivotal Tracker password. ( You will only need to do this once ):");
  $output = shell_exec('curl -u ' . $username . ':' . $password . ' -X GET https://www.pivotaltracker.com/services/v3/tokens/active');
  $matches = array();
  preg_match('/\<guid\>([0-9a-zA-Z]+)\<\/guid\>/', $output, $matches);
  return $matches[1];
}

/**
 * Returns your Pivotal Tracker configuration.
 * 
 * @return <type>
 */
function getConfig() {
  $config = array();
  if( $argc > 1 ) {
    $config['name'] = getArgument("Enter your full name. ( You will only need to do this once ):");
    $config['token'] = getToken();
    $config['project'] = getArgument("Enter you Pivotal Tracker project ID. ( You will only need to do this once ):");
  }
  else {
    $handle = fopen('config', 'a+');
    if ($handle) {
      while (($buffer = fgets($handle, 4096)) !== false) {
        $values = explode('=', $buffer);
        $config[trim($values[0])] = trim($values[1]);
      }
      if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
      }

      if (!isset($config['name'])) {
        $config['name'] = getArgument("Enter your full name. ( You will only need to do this once ):");
        fwrite($handle, 'name=' . $config['name'] . "\n");
      }

      // If there isn't a token...
      if (!isset($config['token'])) {
        $config['token'] = getToken();
        fwrite($handle, 'token=' . $config['token'] . "\n");
      }

      if (!isset($config['project'])) {
        $config['project'] = getArgument("Enter you Pivotal Tracker project ID. ( You will only need to do this once ):");
        fwrite($handle, 'project=' . $config['project'] . "\n");
      }

      fclose($handle);
    }
  }

  return $config;
}

/**
 * Get's the PDF object.
 * 
 * @param <type> $config
 * @param <type> $title
 * @return TCPDF
 */
function getPDF($config, $title) {
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
function getStories($config, $filter) {
  // Now create a new PivotalTracker object.
  $pivotal = new PivotalTracker($config['token']);
  $filter = isset($filter) ? 'label:"' . $filter . '"' : '';
  $filter .= 'includedone:true';
  return $pivotal->stories_get_by_filter($config['project'], $filter);
}

$config = getConfig();
$title = getArgument("Title: ");
$filter = getArgument("Filter: ");
$script = getScript();

// Make sure we have everything.
if ($config['token'] && $config['project'] && $title && $script['selection']) {

  // Convert their selection to an integer.
  settype($script['selection'], "integer");

  // Check the number.
  if (is_integer($script['selection']) && $script['selection'] <= count($script['files'])) {
    // Get the PDF.
    $pdf = getPDF($config, $title);

    // Get the stories.
    $stories = getStories($config, $filter);

    if ($stories) {
      // Include the script.
      require_once($script['files'][($script['selection'] - 1)]);

      // Get the output from our script.
      pdf_contents($pdf, $title, $stories);

      //Close and output PDF document
      $pdf_output = $pdf->Output('doc.pdf', 'S');

      // Now write the contents to a file.
      file_put_contents($title . '.pdf', $pdf_output);
    }
    else {
      echo "No stories found.";
    }
  }
  else {
    echo "\nInvalid Selection.  Please Select a Number.";
  }
}
?>
