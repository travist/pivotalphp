<?php
function getOutput( &$pdf, $title, $stories ) {
  // Set the font of our PDF.
  $pdf->SetFont('times', 'B', 12);

  // add a page
  $pdf->AddPage();

  // Only if there are stories...
  if ($stories) {
    $output = '<html><body>';

    $output .= '<h1>' . $title . '</h2>';

    $tests = '';
    $notests = '<ul>';
    $test_count = 0;
    $total = 0;

    // Now Iterate through the stories.
    foreach ($stories as $story) {
      if (($story['story_type'] != 'release') && ($story['story_type'] != 'chore')) {
        $total++;
        if ( isset($story['tasks']) && count($story['tasks']) > 0) {
          $test_count++;
          $tests .= '<p>';
          $tests .= '<a href="' . $story['url'] . '">' . $story['id'] . '</a>:';
          $tests .= '&nbsp;&nbsp;' . $story['story_type'] . ':&nbsp;&nbsp;<strong>' . $story['name'] . '</strong>';
          $tests .= '<ol>';
          foreach ($story['tasks'] as $tasks) {
            foreach ($tasks as $task) {
              $tests .= '<li>' . $task['description'] . '</li>';
            }
          }
          $tests .= '</ol>';
          $tests .= '</p>';
        }
        else {
          $notests .= '<li>';
          $notests .= '<a href="' . $story['url'] . '">' . $story['id'] . '</a>:';
          $notests .= '&nbsp;&nbsp;' . $story['story_type'] . ':&nbsp;&nbsp;<strong>' . $story['name'] . '</strong>';
          $notests .= '</li>';
        }
      }
    }

    $notests .= '</ul>';

    $summary = '<p>' . $test_count . ' stories out of ' . $total . ' have written test cases.  The following stories are missing test cases.</p>';
    $summary .= $notests;

    // Add the summary and tests.
    $output .= $summary;
    $output .= '<h2>Regression Test Plan</h2>';
    $output .= $tests;

    // Close out the body.
    $output .= '</body></html>';
  }

  return $output;
}
?>