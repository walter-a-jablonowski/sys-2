<?php
// Info list cell renderer
// Variables available: $entry (array with entry data)

$name = htmlspecialchars($entry['name'] ?? 'Unnamed');
$date = formatDateShort($entry['time'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center">
  <div><?php echo $date; ?></div>
  <div><?php echo $name; ?></div>
</div>
