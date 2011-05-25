<?php
function pdf_contents( &$pdf, $args, $stories, &$output ) {
  global $type_cnt;
  global $est_cnt;
  global $est_type_cnt;
  global $est_accept_type_cnt;
  global $est_accept_cnt;
  global $state_type_cnt;
  // Set the font of our PDF.
//  $pdf->SetFont('times', 'B', 12);
  //fixed space font for bullet spacing
  $pdf->SetFont('courier', 'B', 12);


  // add a page
  $pdf->AddPage();

  $output = '<html><body>';

  $output .= '<h2>' . count($stories) . ' total stories with ';
  $output .=  $est_cnt . ' estimated points, ';
  $output .=  $est_accept_cnt . ' accepted points.</h2>';

  //$type_cnt = array('bug'=>0,'feature'=>0,'release'=>0);

$output .= "<table border=\"2\" cellpadding=\"5\"><tr>";
// printing table headers
$output .= "<th bgcolor=\"#00FFFF\">TYPE</th>";
$output .= "<th bgcolor=\"#00FFFF\">Total POINTS  (% of tot)</th>";
$output .= "<th bgcolor=\"#00FFFF\">Accepted POINTS</th>";
$output .= "</tr>\n";

// do each row
foreach ($type_cnt as $the_type => $the_cnt) {
  $output .= "<tr>";
  $output .= "<td>".$the_type."</td>";
  $output .= "<td>";//.$est_type_cnt[$the_type];
  $output .= sprintf("%'06d %s%-6.2f%s",$est_type_cnt[$the_type],'(',$est_type_cnt[$the_type]/$est_cnt*100,'%)');
  $output .="</td>";
  $output .= "<td>";
  $output .= sprintf("%'06d",$est_accept_type_cnt[$the_type]);
  $output .="</td>";
  $output .= "</tr>";
}
  $output .= "<tr>";
  $output .= "<td>POINT TOTALS</td>";
//  $output .= sprintf("%s %'06d %s",'<td>',$est_cnt,'</td>');
  $output .= "<td>";
  $output .= sprintf("%'06d",$est_cnt);
  $output .="</td>";
  $output .= "<td>";
  $output .= sprintf("%'06d",$est_accept_cnt);
  $output .="</td>";
  $output .= "</tr>";
$output .= "</table>\n";

$output .= "<h1>Current State Counts</h1>";
//type state counts
$output .= "<table border=\"2\" cellpadding=\"5\"><tr>";
$states= array ('accepted','delivered','finished','rejected','started');
// printing table headers
$output .= "<th bgcolor=\"#00FFFF\">TYPE</th>";
$output .= "<th bgcolor=\"#00FFFF\">TOTAL</th>";
foreach ($states as $thestate) {
  $output .= "<th bgcolor=\"#00FFFF\">".$thestate."</th>";
}
$output .= "</tr>\n";

// do each row
foreach ($type_cnt as $the_type => $the_cnt) {
  $output .= "<tr>";
  $output .= "<td>".$the_type."</td>";
  $output .= "<td>".$the_cnt."</td>";
  foreach ($states as $thestate) {
    $output .= "<td>".$state_type_cnt[$the_type][$thestate]."</td>";
  }
  $output .= "</tr>";
}
  $output .= "<tr>";
  $output .= "<td>TOTALS</td>";
//no
count($stories) . ' total stories with ';
  $output .= "<td>".count($stories)."</td>";
   foreach ($states as $thestate) {
   $tot = 0;
   foreach ($type_cnt as $the_type => $the_cnt) {
     $tot += $state_type_cnt[$the_type][$thestate];
   }
   $output .= "<td>".$tot."</td>";
  }
  $output .= "</tr>";
$output .= "</table>\n";



// story detail
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
}
?>