<?php
function pdf_contents( &$pdf, $args, $stories ) {
  $i = 0;

  $story_width = 98;
  $story_height = 92;
  $space = 5;
  $num_rows = 3;
  $num_cols = 2;

  $pdf->setCellPaddings(2,2,2,2);
  $pdf->setCellMargins(0,0,0,0);

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
    $pdf->SetFont('times', '', 14);
    $html = '<div>' . $story['id'] . ': <strong>' . $story['name'] . '</strong></div>';
    $html .= (isset($story['description']) && $story['description']) ? '<div style="font-size:30px;">' . $story['description'] . '</div>' : '';
    
    $pdf->writeHTMLCell($story_width, $story_height, $x, $y, $html, 'LRTB', 1, false, true, 'L', true);

    // Write the end of table.
    $pdf->SetFont('times', '', 10);

    if( isset($story['story_type']) ) {
      $pdf->Text($x, $y + 70, 'Type: ' . $story['story_type']);
    }

    if( isset($story['current_state']) ) {
      $pdf->Text($x + 35, $y + 70, 'Status: ' . $story['current_state']);
    }
    
    if( isset($story['estimate']) ) {
      $pdf->Text($x + 70, $y + 70, 'Points: ' . $story['estimate']);
    }

    if( isset($story['labels']) ) {
      $pdf->Text($x, $y + 75, 'Labels: ' . $story['labels']);
    }

    if( isset($story['requested_by']) ) {
      $pdf->Text($x, $y + 80, 'Requester: ' . $story['requested_by']);
    }
    
    if( isset($story['owned_by']) ) {
      $pdf->Text($x + 60, $y + 80, 'Owner: ' . $story['owned_by']);
    }

    if( $cell == 5 ) {
      $pdf->lastPage();
    }

    $i++;
  }
}
?>