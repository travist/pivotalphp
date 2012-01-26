<?php
function pdf_contents( &$pdf, $args, $stories, &$output ) {
  $pdf->SetFont('courier', 'B', 12);
  $pdf->AddPage();

  $output = '<html><body>';
  $output .= '<h1>' . $args['title'] . '</h2>';

  $dev_stories = array();

  // Iterate through the stories.
  foreach ($stories as $story) {
    if (($story['story_type'] != 'release')) {

      // Create the dev stories array.
      if (!isset($dev_stories[$story['owned_by']])) {
        $dev_stories[$story['owned_by']] = array();
      }
      // Add this story.
      $dev_stories[$story['owned_by']][$story['id']] = array(
        'name' => $story['name'],
        'url' => $story['url'],
        'estimate' => $story['estimate'],
        'story_type' => $story['story_type']
      );
    }
  }

  $table .= '<table border="2" cellpadding="5">';
  $table .= '<tr><th>Developer</th><th>Story</th><th>Estimate</th><th>Diff</th><th>Pull Request</th><th>Story Type</th></tr>';
  $velocities = array();
  foreach ($dev_stories as $owner => $dstories) {
    $velocities[$owner] = 0;
    foreach ($dstories as $id => $dstory) {
      $info = shell_exec('git log | grep "Merge pull request.*' . $id . '" -B 5');
      $matches = array();
      if ($info) {
        preg_match('/commit ([0-9a-f]+).*Merge pull request \#([0-9]+)/s', $info, $matches);
      }
      $sha = $matches[1] ? trim($matches[1]) : '';
      $pull = $matches[2] ? trim($matches[2]) : '';
      $velocities[$owner] += $dstory['estimate'];
      $table .= '<tr>';
      $table .= '<td>' . $owner . '</td>';
      $table .= '<td><a href="https://www.pivotaltracker.com/story/show/' . $id . '">sid-' . $id . '</a>:&nbsp;&nbsp;' . $dstory['name'] . '</td>';
      $table .= '<td>' . $dstory['estimate'] . '</td>';
      $table .= $sha ? '<td><a target="_blank" href="https://github.com/AllPlayers/allplayers-mainline/commit/' . $sha . '">DIFF</a></td>' : '<td></td>';
      $table .= $pull ? '<td><a target="_blank" href="https://github.com/AllPlayers/allplayers-mainline/pull/' . $pull . '">PULL</a></td>' : '<td></td>';
      $table .= '<td>' . $dstory['story_type'] . '</td>';
      $table .= '</tr>';
    }
    $table .= '<tr><td bgcolor="#33FF33">Total Velocity</td><td bgcolor="#33FF33" colspan="4">' . $velocities[$owner] . '</td></tr>';
  }
  $table .= '</table>';

  $output .= '<table border="2" cellpadding="5">';
  $output .= '<tr><th bgcolor="#FFFF00">Developer</th><th bgcolor="#FFFF00">Total Velocity</th></tr>';
  foreach ($velocities as $owner => $total) {
    $output .= '<tr>';
    $output .= '<td>' . $owner . '</td>';
    $output .= '<td>' . $total . '</td>';
    $output .= '</tr>';
  }
  $output .= '</table>' . "\n<br />\n&nbsp;<br />\n";

  // Add the velocity table.
  $output .= $table;

  // Close out the body.
  $output .= '</body></html>';
}
?>