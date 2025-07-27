<?php
// Edit form for Info type
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
    <textarea class="form-control" name="description" rows="10"><?= htmlspecialchars($entry['description']) ?></textarea>
  </div>
  
  <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="saveEntry()">Save</button>
  </div>
</form>
