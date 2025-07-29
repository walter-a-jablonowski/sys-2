<?php
// Apartment list cell renderer

$name = $entry['name'] ?? 'Untitled';
$state = $entry['state'] ?? 'new';
$time = $entry['time'] ?? '';
$filesNr = $entry['files_nr'] ?? '';
$url = $entry['url'] ?? '';
$date = $time ? date('Y-m-d', strtotime($time)) : '';

$nameDisplay = $url ? "<a href=\"$url\" target=\"_blank\" onclick=\"event.stopPropagation()\">" . htmlspecialchars($name) . "</a>" : htmlspecialchars($name);
?>

<div>
  <div class="d-flex justify-content-between align-items-center mb-1">
    <div class="fw-medium"><?= $nameDisplay ?></div>
    <span class="badge state-badge state-<?= $state ?>"><?= ucfirst($state) ?></span>
  </div>
  <div class="d-flex justify-content-between align-items-center entry-meta">
    <small><?= $date ?></small>
    <small><?= $filesNr ?></small>
  </div>
</div>
