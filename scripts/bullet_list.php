<?php
function pdf_contents( &$pdf, $args, $stories ) {
  global $type_cnt;
  // Set the font of our PDF.
//  $pdf->SetFont('times', 'B', 12);
  //fixed space font for bullet spacing
  $pdf->SetFont('courier', 'B', 12);


  // add a page
  $pdf->AddPage();

  $output = '<html><body>';

  $output .= '<h2>' . count($stories) . ' total stories</h2>';

  //$type_cnt = array('bug'=>0,'feature'=>0,'release'=>0);

$output .= "<table border=\"2\" cellpadding=\"5\"><tr>";
// printing table headers
$output .= "<th bgcolor=\"#00FFFF\">TYPE</th><th bgcolor=\"#00FFFF\">COUNT</th>";
$output .= "</tr>\n";

// do each row
foreach ($type_cnt as $the_type => $the_cnt) {
  $output .= "<tr>";
  $output .= "<td>".$the_type."</td>";
  $output .= "<td>".$the_cnt."</td>";
  $output .= "</tr>";
}
$output .= "</table>\n";


$table_col = array ('id','story_type','name','estimate','requested_by','owned_by','current_state','labels');

//$output .= "<h1>Table: {$table}</h1>";

$output .= "<h1>Story Detail</h1>";

$output .= "<table border=\"2\" cellpadding=\"5\"><tr>";
// printing table headers
foreach ($table_col as $col_name) {
    $output .= "<th bgcolor=\"#FFFF00\"><b>";
    $output .= $col_name;
    $output .= "</b></th>";
}
$output .= "</tr>\n";

// printing table rows
foreach ($stories as $story) 
{
  if (($story['story_type'] != 'release') && ($story['story_type'] != 'chore')) {
   $output .= "<tr>";
   // do each row
   foreach ($table_col as $col_name) {
     $output .= "<td>";
     if ($col_name == "id") {
       $output .= '<a href="' . $story['url'] . '">';
       $output .= $story[$col_name];
       $output .= '</a>';
     }
     else {
       $output .= $story[$col_name];
     }
     $output .= "</td>";
   }
   $output .= "</tr>\n";
  }
}
$output .= "</table>\n<br />\n".'&nbsp;'." <br />\n";

$output .= '<ul>';
  // Now Iterate through the stories.
  foreach ($stories as $story) {
    if (($story['story_type'] != 'release') && ($story['story_type'] != 'chore')) {
      $output .= '<li>';
      $output .= '<a href="' . $story['url'] . '">';
      $pad_str =sprintf("%-10.10s",$story['id']);
      //print $pad_str ."\n";
      //print str_replace (' ' , '&nbsp;',$pad_str) ."\n";
      $output .=  str_replace (' ' , '&nbsp;',$pad_str);
      $output .= '</a>:';
      $output .= '&nbsp;&nbsp;';
      $pad_str =sprintf("%-10.10s",$story['story_type']);
      $output .=  str_replace (' ' , '&nbsp;',$pad_str);
      $output .= ':&nbsp;&nbsp;<strong>' . $story['name'] . '</strong>';
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