<div class="d-flex w-100 justify-content-between">
  <div>
    <small class="text-muted me-3"><?= date('m-d', strtotime($entry_data['time'] ?? 'now')) ?></small>
    <span><?= htmlspecialchars($entry_data['name'] ?? '') ?></span>
  </div>
</div>
<div class="btn-group" role="group" style="position: absolute; top: 5px; right: 5px;">
  <button type="button" class="btn btn-sm btn-primary edit-button" data-bs-toggle="modal" data-bs-target="#editEntryModal" data-path="<?= htmlspecialchars($entry_data['path']) ?>">Edit</button>
  <button type="button" class="btn btn-sm btn-danger delete-button">Delete</button>
</div>
