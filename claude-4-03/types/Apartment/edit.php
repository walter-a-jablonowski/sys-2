<?php
// Apartment edit renderer for modal

$name = $entry['name'] ?? '';
$description = $entry['description'] ?? '';
$priority = $entry['priority'] ?? 3;
$state = $entry['state'] ?? 'new';
$dueDate = $entry['dueDate'] ?? '';
$result = $entry['result'] ?? '';
$filesNr = $entry['files_nr'] ?? '';
$url = $entry['url'] ?? '';
?>

<form id="editApartmentForm">
  <input type="hidden" id="editEntryPath" value="<?= htmlspecialchars($entry['path']) ?>">
  <input type="hidden" id="editEntryType" value="Apartment">
  
  <div class="mb-3">
    <label for="editApartmentName" class="form-label">Name</label>
    <input type="text" class="form-control" id="editApartmentName" value="<?= htmlspecialchars($name) ?>" required>
  </div>
  
  <div class="row mb-3">
    <div class="col-6">
      <label for="editApartmentPriority" class="form-label">Priority</label>
      <select class="form-select" id="editApartmentPriority" required>
        <?php for( $i = 1; $i <= 5; $i++ ): ?>
        <option value="<?= $i ?>" <?= $priority == $i ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-6">
      <label for="editApartmentState" class="form-label">State</label>
      <select class="form-select" id="editApartmentState" required>
        <option value="new" <?= $state === 'new' ? 'selected' : '' ?>>New</option>
        <option value="current" <?= $state === 'current' ? 'selected' : '' ?>>Current</option>
        <option value="maybe" <?= $state === 'maybe' ? 'selected' : '' ?>>Maybe</option>
        <option value="done" <?= $state === 'done' ? 'selected' : '' ?>>Done</option>
      </select>
    </div>
  </div>
  
  <div class="row mb-3">
    <div class="col-6">
      <label for="editApartmentFilesNr" class="form-label">Files Nr</label>
      <input type="text" class="form-control" id="editApartmentFilesNr" value="<?= htmlspecialchars($filesNr) ?>" readonly>
    </div>
    <div class="col-6">
      <label for="editApartmentDueDate" class="form-label">Due Date (optional)</label>
      <input type="datetime-local" class="form-control" id="editApartmentDueDate" value="<?= $dueDate ? date('Y-m-d\TH:i', strtotime($dueDate)) : '' ?>">
    </div>
  </div>
  
  <div class="mb-3">
    <label for="editApartmentUrl" class="form-label">URL</label>
    <input type="url" class="form-control" id="editApartmentUrl" value="<?= htmlspecialchars($url) ?>">
  </div>
  
  <div class="mb-3">
    <label for="editApartmentResult" class="form-label">Result</label>
    <input type="text" class="form-control" id="editApartmentResult" value="<?= htmlspecialchars($result) ?>">
  </div>
  
  <div class="mb-3">
    <label for="editApartmentDescription" class="form-label">Description</label>
    <textarea class="form-control" id="editApartmentDescription" rows="3"><?= htmlspecialchars($description) ?></textarea>
  </div>
  
  <!-- Camera/Image Upload Section -->
  <div class="mb-3">
    <label class="form-label">Add Images</label>
    <div class="d-flex gap-2">
      <input type="file" class="form-control" id="apartmentImageUpload" accept="image/*" capture="camera" multiple>
      <button type="button" class="btn btn-outline-primary" id="uploadImages">
        <i class="bi bi-camera"></i> Upload
      </button>
    </div>
    <small class="text-muted">Take photos with your camera or select existing images</small>
  </div>
</form>
