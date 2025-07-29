<?php
// Info list cell renderer

$time = $entry['time'] ?? '';
$name = $entry['name'] ?? 'Untitled';
$date = $time ? date('m-d', strtotime($time)) : '';
?>

<div class="d-flex justify-content-between align-items-center">
  <div class="entry-meta"><?= $date ?></div>
  <div class="fw-medium"><?= htmlspecialchars($name) ?></div>
</div>
