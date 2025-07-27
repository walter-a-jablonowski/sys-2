<?php
// Edit form for Apartment type
$files = json_decode($entry['data']['files_nr'] ?? '[]', true) ?: [];
?>
<form id="editForm">
  <input type="hidden" name="id" value="<?= htmlspecialchars($entry['id']) ?>">
  <input type="hidden" name="path" value="<?= htmlspecialchars($entry['path']) ?>">
  <input type="hidden" name="type" value="<?= htmlspecialchars($entry['type']) ?>">
  
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($entry['name']) ?>">
  </div>
  
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="5"><?= htmlspecialchars($entry['description']) ?></textarea>
  </div>
  
  <div class="mb-3">
    <label class="form-label">State</label>
    <select class="form-select" name="state">
      <option value="searching" <?= ($entry['data']['state'] ?? '') === 'searching' ? 'selected' : '' ?>>Searching</option>
      <option value="viewing" <?= ($entry['data']['state'] ?? '') === 'viewing' ? 'selected' : '' ?>>Viewing</option>
      <option value="applied" <?= ($entry['data']['state'] ?? '') === 'applied' ? 'selected' : '' ?>>Applied</option>
      <option value="rejected" <?= ($entry['data']['state'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
      <option value="accepted" <?= ($entry['data']['state'] ?? '') === 'accepted' ? 'selected' : '' ?>>Accepted</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Result/Notes</label>
    <textarea class="form-control" name="result" rows="3"><?= htmlspecialchars($entry['data']['result'] ?? '') ?></textarea>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Listing URL</label>
    <input type="url" class="form-control" name="url" value="<?= htmlspecialchars($entry['data']['url'] ?? '') ?>">
  </div>
  
  <div class="mb-3">
    <label class="form-label">Images</label>
    <button type="button" class="btn btn-primary btn-sm" onclick="uploadCameraImage('<?= $entry['id'] ?>', '<?= $entry['path'] ?>')">
      <i class="bi bi-camera"></i> Upload Camera Image
    </button>
    
    <?php if (!empty($files)): ?>
      <div class="row mt-2">
        <?php foreach ($files as $file): ?>
          <div class="col-6 col-md-4 mb-2">
            <div class="position-relative">
              <img src="data/<?= htmlspecialchars($entry['path']) ?>/<?= htmlspecialchars($file) ?>" 
                   class="img-fluid rounded" alt="Image">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="saveEntry()">Save</button>
  </div>
</form>
