<?php
// Info edit form renderer
$isEdit = isset($entry);
$name = $isEdit ? ($entry['name'] ?? '') : '';
$description = $isEdit ? ($entry['description'] ?? '') : '';
$path = $isEdit ? ($entry['_path'] ?? '') : '';
?>

<form id="<?= $isEdit ? 'editEntryForm' : 'addEntryForm' ?>">
  <div class="mb-3">
    <label for="name" class="form-label">Name *</label>
    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
  </div>
  
  <input type="hidden" name="type" value="Info">
  
  <div class="d-flex justify-content-end">
    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="<?= $isEdit ? "submitEditForm('Info', '$path')" : "submitAddForm('Info')" ?>">
      <?= $isEdit ? 'Update' : 'Create' ?>
    </button>
  </div>
</form>
