<?php
function pdf_contents( &$pdf, $args, $stories ) {
  // Set the font of our PDF.
  $pdf->SetFont('times', 'B', 12);

  // add a page
  $pdf->AddPage();

  $output = '<html><body>';

  $output .= '<h1>' . $args['title'] . '</h2>';

  $tests = '';
  $notests = '<ul>';
  $test_count = 0;
  $total = 0;

  // Now Iterate through the stories.
  foreach ($stories as $story) {
    if (($story['story_type'] != 'release') && ($story['story_type'] != 'chore')) {
      if ( isset($story['tasks']) && count($story['tasks']) > 0) {
        $total++;
        $test_count++;
        $tests .= '<a href="' . $story['url'] . '">' . $story['id'] . '</a>:';
        $tests .= '&nbsp;&nbsp;' . $story['story_type'] . ':&nbsp;&nbsp;<strong>' . $story['name'] . '</strong>';
        $tests .= '<ol>';
        foreach ($story['tasks'] as $tasks) {
          if( isset($tasks['description']) ) {
            $tests .= '<li>' . $tasks['description'] . '</li>';
          }
          else {
            foreach ($tasks as $task) {
              $tests .= '<li>' . $task['description'] . '</li>';
            }
          }
        }
        $tests .= '</ol>';
      }
      else if( in_array($story['current_state'], array('delivered', 'finished', 'accepted')) ) {
        $total++;
        $owned_by = isset($story['owned_by']) ? $story['owned_by'] : 'none';
        $notests .= '<li>';
        $notests .= '<a href="' . $story['url'] . '">' . $story['id'] . '</a>:&nbsp;&nbsp;';
        $notests .= '<strong>' . $owned_by . '</strong>';
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

  // Write the HTML to this page.
  $pdf->writeHTML($output);
}
?>