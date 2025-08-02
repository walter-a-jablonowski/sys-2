<?php
// Apartment read-only renderer
$state = $entry['state'] ?? 'new';
$stateClass = 'state-' . $state;
$files_nr = $entry['files_nr'] ?? '0000';
?>

<div class="type-apartment <?= $stateClass ?>">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <h5 class="mb-0"><?= htmlspecialchars($entry['name']) ?></h5>
    <span class="badge bg-secondary"><?= ucfirst($state) ?></span>
  </div>
  
  <div class="row mb-2">
    <div class="col-6">
      <strong>Files Nr:</strong> <?= htmlspecialchars($files_nr) ?>
    </div>
    <div class="col-6">
      <strong>Created:</strong> <?= htmlspecialchars($entry['time']) ?>
    </div>
  </div>
  
  <?php if( !empty($entry['url']) ): ?>
  <p class="mb-2">
    <strong>URL:</strong> 
    <a href="<?= htmlspecialchars($entry['url']) ?>" target="_blank" class="text-decoration-none">
      🔗 <?= htmlspecialchars($entry['url']) ?>
    </a>
  </p>
  <?php endif; ?>
  
  <?php if( !empty($entry['result']) ): ?>
  <p class="mb-2">
    <strong>Result:</strong> <?= htmlspecialchars($entry['result']) ?>
  </p>
  <?php endif; ?>
  
  <?php if( isset($entry['description']) && $entry['description'] ): ?>
  <div class="mt-3">
    <strong>Description:</strong>
    <div class="mt-2">
      <?= nl2br(htmlspecialchars($entry['description'])) ?>
    </div>
  </div>
  <?php endif; ?>
</div>
