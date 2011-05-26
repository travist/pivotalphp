<?php
function storycard_contents( &$pdf, $args, $stories, &$output ) {
  $i = 0;

  $output = '<html><body>';
  $story_width = 98;
  $story_height = 92;
  $space = 5;
  $num_rows = 3;
  $num_cols = 2;

  // Now Iterate through the stories.
  foreach ($stories as $story) {

    // Six cells per page.
    $cell = ($i % ($num_rows * $num_cols));
    $col = $cell % $num_cols;
    $row = floor($cell / $num_cols);
    $x = $col*$story_width + (($col+1)*$space);
    $y = $row*$story_height + (($row+1)*$space);

    if( $cell == 0 ) {
      $pdf->AddPage();
    }

    // Write the title.

    $html = '<div style="border:1px solid black;">' . $story['id'] . ': <h3><strong>' . $story['name'] . '</strong></h3><br/>';
    $html .= (isset($story['description']) && $story['description']) ? $story['description'] : '';
    $html .= '<br/>';

    $pdf->writeHTMLCell($story_width, $story_height, $x, $y, $html, 'LRTB', 1, false, true, 'L', true);

    // Write the end of table.
    $pdf->SetFont('times', '', 10);

    if( isset($story['story_type']) ) {
      $html .= ('<br/>Type: ' . $story['story_type']);
    }

    if( isset($story['current_state']) ) {
      $html .= ('<br/>Status: ' . $story['current_state']);
    }

    if( isset($story['estimate']) ) {
      $html .= ('<br/>Points: ' . $story['estimate']);
    }

    if( isset($story['labels']) ) {
      $html .= ('<br/>Labels: ' . $story['labels']);
    }

    if( isset($story['requested_by']) ) {
      $html .= ('<br/>Requester: ' . $story['requested_by']);
    }

    if( isset($story['owned_by']) ) {
      $html .= ('<br/>Owner: ' . $story['owned_by']);
    }

    $html .= '</div><br/>';
    $output .= $html;

    $i++;
  }
}
?>
