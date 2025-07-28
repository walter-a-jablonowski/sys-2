<div class="d-flex w-100 justify-content-between">
  <div>
    <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($entry_data['priority'] ?? 'N/A'); ?></span>
    <strong class="mb-1"><?php echo htmlspecialchars($entry_data['name']); ?></strong>
  </div>
  <small><?php echo htmlspecialchars($entry_data['state'] ?? ''); ?></small>
</div>
<div class="btn-group" role="group" style="position: absolute; top: 5px; right: 5px;">
  <button type="button" class="btn btn-sm btn-primary edit-button" data-bs-toggle="modal" data-bs-target="#editEntryModal" data-path="<?php echo htmlspecialchars($entry_data['path']); ?>">Edit</button>
  <button type="button" class="btn btn-sm btn-danger delete-button">Delete</button>
</div>
