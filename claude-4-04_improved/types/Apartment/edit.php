<?php
// Apartment edit form renderer
$isEdit = isset($entry);
$name = $isEdit ? ($entry['name'] ?? '') : '';
$state = $isEdit ? ($entry['state'] ?? 'new') : 'new';
$result = $isEdit ? ($entry['result'] ?? '') : '';
$files_nr = $isEdit ? ($entry['files_nr'] ?? '') : '';
$url = $isEdit ? ($entry['url'] ?? '') : '';
$description = $isEdit ? ($entry['description'] ?? '') : '';
$path = $isEdit ? ($entry['_path'] ?? '') : '';
?>

<form id="<?= $isEdit ? 'editEntryForm' : 'addEntryForm' ?>">
  <div class="mb-3">
    <label for="name" class="form-label">Name *</label>
    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="state" class="form-label">State *</label>
    <select class="form-select" id="state" name="state" required>
      <option value="new" <?= $state == 'new' ? 'selected' : '' ?>>New</option>
      <option value="current" <?= $state == 'current' ? 'selected' : '' ?>>Current</option>
      <option value="maybe" <?= $state == 'maybe' ? 'selected' : '' ?>>Maybe</option>
      <option value="done" <?= $state == 'done' ? 'selected' : '' ?>>Done</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label for="files_nr" class="form-label">Files Nr *</label>
    <input type="text" class="form-control" id="files_nr" name="files_nr" value="<?= htmlspecialchars($files_nr) ?>" 
           pattern="^\d{4}$" placeholder="0001" maxlength="4" required>
    <div class="form-text">4 digits with leading zeros (e.g., 0001)</div>
  </div>
  
  <div class="mb-3">
    <label for="url" class="form-label">URL</label>
    <input type="url" class="form-control" id="url" name="url" value="<?= htmlspecialchars($url) ?>">
  </div>
  
  <div class="mb-3">
    <label for="result" class="form-label">Result</label>
    <input type="text" class="form-control" id="result" name="result" value="<?= htmlspecialchars($result) ?>">
  </div>
  
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
  </div>
  
  <!-- Image Upload Section -->
  <div class="mb-3">
    <label for="apartmentImage" class="form-label">Apartment Images</label>
    <div class="input-group">
      <input type="file" class="form-control" id="apartmentImage" name="apartmentImage" 
             accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple
             onchange="showImagePreview(this)">
      <button type="button" class="btn btn-outline-secondary" onclick="captureImage('apartmentImage')">
        📷 Camera
      </button>
    </div>
    <div class="form-text">Supported formats: JPG, PNG, GIF, WebP</div>
  </div>
  
  <input type="hidden" name="type" value="Apartment">
  
  <div class="d-flex justify-content-end">
    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="<?= $isEdit ? "submitEditForm('Apartment', '$path')" : "submitAddForm('Apartment')" ?>">
      <?= $isEdit ? 'Update' : 'Create' ?>
    </button>
  </div>
</form>

<script>
function initApartmentForm() {
  // Auto-generate files_nr if not editing
  <?php if( !$isEdit ): ?>
  generateFilesNr();
  <?php endif; ?>
}

async function generateFilesNr() {
  try {
    const result = await ajaxCall('getNextFileNumber', {}, 'Apartment');
    document.getElementById('files_nr').value = result.files_nr;
  } catch (error) {
    console.error('Failed to generate files_nr:', error);
  }
}
</script>
