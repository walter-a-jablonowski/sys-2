<?php
// Apartment read-only renderer using PHP's alternative syntax

$name = $entry['name'] ?? 'Untitled';
$time = $entry['time'] ?? '';
$description = $entry['description'] ?? '';
$priority = $entry['priority'] ?? 3;
$state = $entry['state'] ?? 'new';
$dueDate = $entry['dueDate'] ?? '';
$result = $entry['result'] ?? '';
$filesNr = $entry['files_nr'] ?? '';
$url = $entry['url'] ?? '';
?>

<div class="apartment-readonly">
  <h5>
    <?php if( $url ): ?>
    <a href="<?= htmlspecialchars($url) ?>" target="_blank"><?= htmlspecialchars($name) ?></a>
    <?php else: ?>
    <?= htmlspecialchars($name) ?>
    <?php endif; ?>
  </h5>
  
  <div class="row mb-3">
    <div class="col-4">
      <small class="text-muted">Priority:</small><br>
      <span class="badge bg-primary"><?= $priority ?></span>
    </div>
    <div class="col-4">
      <small class="text-muted">State:</small><br>
      <span class="badge state-badge state-<?= $state ?>"><?= ucfirst($state) ?></span>
    </div>
    <div class="col-4">
      <small class="text-muted">Files Nr:</small><br>
      <span class="badge bg-secondary"><?= $filesNr ?></span>
    </div>
  </div>
  
  <?php if( $url ): ?>
  <div class="mb-3">
    <small class="text-muted">URL:</small><br>
    <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-break"><?= htmlspecialchars($url) ?></a>
  </div>
  <?php endif; ?>
  
  <?php if( $result ): ?>
  <div class="mb-3">
    <small class="text-muted">Result:</small><br>
    <span><?= htmlspecialchars($result) ?></span>
  </div>
  <?php endif; ?>
  
  <?php if( $dueDate ): ?>
  <div class="mb-3">
    <small class="text-muted">Due Date:</small><br>
    <span><?= htmlspecialchars($dueDate) ?></span>
  </div>
  <?php endif; ?>
  
  <div class="mb-3">
    <small class="text-muted">Created:</small><br>
    <span><?= htmlspecialchars($time) ?></span>
  </div>
  
  <?php if( $description ): ?>
  <div class="mb-3">
    <small class="text-muted">Description:</small><br>
    <p><?= nl2br(htmlspecialchars($description)) ?></p>
  </div>
  <?php endif; ?>
</div>
