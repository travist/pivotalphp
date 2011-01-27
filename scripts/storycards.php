<?php
function pdf_contents( &$pdf, $title, $stories ) {
  $i = 0;

  print_r($stories);

  // Now Iterate through the stories.
  foreach ($stories as $story) {

    // Six cells per page.
    $cell = ($i % 6);
    $x = $y = 0;
    switch( $cell ) {
      case 0:
        $x = 5;
        $y = 5;
        break;
      case 1:
        $x = 107;
        $y = 5;
        break;
      case 2:
        $x = 5;
        $y = 92;
        break;
      case 3:
        $x = 107;
        $y = 92;
        break;
      case 4:
        $x = 5;
        $y = 179;
        break;
      case 5:
        $x = 107;
        $y = 179;
        break;      
    }

    if( $cell == 0 ) {
      $pdf->AddPage();
    }

    // Write the title.
    $pdf->SetFont('times', '', 10);
    $html = '<div>' . $story['id'] . ': <strong>' . $story['name'] . '</strong></div>';
    $html .= (isset($story['description']) && $story['description']) ? '<div>' . $story['description'] . '</div>' : '';
    
    // Write the end of table.
    $pdf->writeHTMLCell(100, 85, $x, $y, $html, 'LRTB', 1, false, true, 'L', true);

    // Write the end of table.
    $pdf->Text($x, $y + 70, 'Type: ' . $story['story_type']);
    $pdf->Text($x + 35, $y + 70, 'Status: ' . $story['current_state']);
    $pdf->Text($x + 70, $y + 70, 'Points: ' . $story['estimate']);
    $pdf->Text($x, $y + 75, 'Labels: ' . $story['labels']);
    $pdf->Text($x, $y + 80, 'Requester: ' . $story['requested_by']);
    $pdf->Text($x + 60, $y + 80, 'Owner: ' . $story['owned_by']);

    if( $cell == 5 ) {
      $pdf->lastPage();
    }

    $i++;
  }
}
?>