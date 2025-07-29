<h5><?= htmlspecialchars($currentInstance['name']) ?></h5>

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
