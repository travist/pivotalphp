<?php
function pdf_contents( &$pdf, $title, $stories ) {
  // Set the font of our PDF.
  $pdf->SetFont('times', 'B', 12);

  // add a page
  $pdf->AddPage();

  $output = '<html><body>';

  $output .= '<h2>' . count($stories) . ' total stories</h2>';
  $output .= '<ul>';

  // Now Iterate through the stories.
  foreach ($stories as $story) {
    if (($story['story_type'] != 'release') && ($story['story_type'] != 'chore')) {
      $output .= '<li>';
      $output .= '<a href="' . $story['url'] . '">' . $story['id'] . '</a>:';
      $output .= '&nbsp;&nbsp;' . $story['story_type'] . ':&nbsp;&nbsp;<strong>' . $story['name'] . '</strong>';
      $output .= '</li>';
    }
  }

  $output .= '</ul>';

  // Close out the body.
  $output .= '</body></html>';

  // Write the HTML to this page.
  $pdf->writeHTML($output);
}
?>