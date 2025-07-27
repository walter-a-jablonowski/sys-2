<?php
// List cell renderer for Apartment type
$state = $entry['data']['state'] ?? 'searching';
$url = $entry['data']['url'] ?? '';
$imageCount = 0;

if (!empty($entry['data']['files_nr']))
{
  $files = json_decode($entry['data']['files_nr'], true) ?: [];
  $imageCount = count($files);
}

$stateBadge = '';
switch ($state)
{
  case 'searching':
    $stateBadge = '<span class="badge bg-primary">Searching</span>';
    break;
  case 'viewing':
    $stateBadge = '<span class="badge bg-info">Viewing</span>';
    break;
  case 'applied':
    $stateBadge = '<span class="badge bg-warning">Applied</span>';
    break;
  case 'rejected':
    $stateBadge = '<span class="badge bg-danger">Rejected</span>';
    break;
  case 'accepted':
    $stateBadge = '<span class="badge bg-success">Accepted</span>';
    break;
}

?>
<div>
  <h6 class="mb-1"><?= htmlspecialchars($entry['name']) ?></h6>
  <div class="mb-1">
    <?= $stateBadge ?>
    <?php if ($imageCount > 0): ?>
      <span class="badge bg-secondary">
        <i class="bi bi-camera"></i> <?= $imageCount ?>
      </span>
    <?php endif; ?>
  </div>
  <?php if ($url): ?>
    <small class="text-muted d-block text-truncate">
      <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-decoration-none">
        <i class="bi bi-link-45deg"></i> <?= htmlspecialchars($url) ?>
      </a>
    </small>
  <?php endif; ?>
  <small class="text-muted d-block"><?= htmlspecialchars($entry['type']) ?></small>
</div>
