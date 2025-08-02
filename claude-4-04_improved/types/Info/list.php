<?php
// Info list renderer
$date = isset($entry['time']) ? date('m-d', strtotime($entry['time'])) : '';
?>

<div class="d-flex justify-content-between align-items-center type-info">
  <div>
    <span class="me-2"><?= htmlspecialchars($date) ?></span>
    <span><?= htmlspecialchars($entry['name']) ?></span>
  </div>
</div>
