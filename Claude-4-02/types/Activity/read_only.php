<h5><?= htmlspecialchars($currentInstance['name']) ?></h5>

<div class="row mb-2">
  <div class="col-sm-3"><strong>Priority:</strong></div>
  <div class="col-sm-9">
    <?php 
    $priority = $currentInstance['priority'] ?? 1;
    $badgeClass = match($priority) {
      1 => 'bg-success',
      2 => 'bg-info', 
      3 => 'bg-warning',
      4 => 'bg-danger',
      5 => 'bg-dark',
      default => 'bg-secondary'
    };
    ?>
    <span class="badge <?= $badgeClass ?>"><?= $priority ?></span>
  </div>
</div>

<div class="row mb-2">
  <div class="col-sm-3"><strong>State:</strong></div>
  <div class="col-sm-9">
    <?php 
    $state = $currentInstance['state'] ?? 'new';
    $stateBadgeClass = match($state) {
      'new' => 'bg-secondary',
      'progress' => 'bg-primary',
      'done' => 'bg-success',
      default => 'bg-secondary'
    };
    $stateLabel = match($state) {
      'new' => 'New',
      'progress' => 'In Progress', 
      'done' => 'Done',
      default => ucfirst($state)
    };
    ?>
    <span class="badge <?= $stateBadgeClass ?>"><?= $stateLabel ?></span>
  </div>
</div>

<?php if( isset($currentInstance['dueDate']) && ! empty($currentInstance['dueDate']) ): ?>
<div class="row mb-2">
  <div class="col-sm-3"><strong>Due Date:</strong></div>
  <div class="col-sm-9"><?= htmlspecialchars($currentInstance['dueDate']) ?></div>
</div>
<?php endif; ?>

<div class="row mb-2">
  <div class="col-sm-3"><strong>Created:</strong></div>
  <div class="col-sm-9"><?= htmlspecialchars($currentInstance['time']) ?></div>
</div>

<?php if( isset($currentInstance['description']) && ! empty($currentInstance['description']) ): ?>
<div class="row">
  <div class="col-sm-3"><strong>Description:</strong></div>
  <div class="col-sm-9"><?= nl2br(htmlspecialchars($currentInstance['description'])) ?></div>
</div>
<?php endif; ?>
