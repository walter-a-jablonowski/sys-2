<?php
// Info edit renderer for modal

$name = $entry['name'] ?? '';
$description = $entry['description'] ?? '';
?>

<form id="editInfoForm">
  <input type="hidden" id="editEntryPath" value="<?= htmlspecialchars($entry['path']) ?>">
  <input type="hidden" id="editEntryType" value="Info">
  
  <div class="mb-3">
    <label for="editInfoName" class="form-label">Name</label>
    <input type="text" class="form-control" id="editInfoName" value="<?= htmlspecialchars($name) ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="editInfoDescription" class="form-label">Description</label>
    <textarea class="form-control" id="editInfoDescription" rows="3"><?= htmlspecialchars($description) ?></textarea>
  </div>
</form>
