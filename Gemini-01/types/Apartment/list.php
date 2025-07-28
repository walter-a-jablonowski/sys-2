<div>
  <div class="d-flex w-100 justify-content-between">
    <h6 class="mb-1">
      <?php if ( ! empty($entry_data['url']) ): ?>
        <a href="<?php echo htmlspecialchars($entry_data['url']); ?>" target="_blank">
          <?php echo htmlspecialchars($entry_data['name'] ?? ''); ?> <i class="bi bi-box-arrow-up-right"></i>
        </a>
      <?php else: ?>
        <?php echo htmlspecialchars($entry_data['name'] ?? ''); ?>
      <?php endif; ?>
    </h6>
    <span class="badge bg-primary"><?php echo htmlspecialchars($entry_data['state'] ?? ''); ?></span>
  </div>
  <div class="d-flex w-100 justify-content-between text-muted">
    <small><?= date('Y-m-d', strtotime($entry_data['time'] ?? 'now')) ?></small>
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-sm btn-primary edit-button" data-bs-toggle="modal" data-bs-target="#editEntryModal" data-path="<?= htmlspecialchars($entry_data['path']) ?>">Edit</button>
      <button type="button" class="btn btn-sm btn-danger delete-button">Delete</button>
    </div>
    <small><?php echo htmlspecialchars($entry_data['files_nr'] ?? ''); ?></small>
  </div>
</div>
