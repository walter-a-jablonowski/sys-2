<?php
// Apartment edit form renderer
// Variables available: $instance (array with instance data)

$name = htmlspecialchars($instance['name'] ?? '');
$description = htmlspecialchars($instance['description'] ?? '');
$state = $instance['state'] ?? 'new';
$result = htmlspecialchars($instance['result'] ?? '');
$filesNr = htmlspecialchars($instance['files_nr'] ?? '0000');
$url = htmlspecialchars($instance['url'] ?? '');
?>

<form id="editForm">
  <div class="mb-3">
    <label for="name" class="form-label">Name *</label>
    <input type="text" class="form-control" name="name" value="<?php echo $name; ?>" required>
  </div>
  
  <div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"><?php echo $description; ?></textarea>
  </div>
  
  <div class="row">
    <div class="col-md-6">
      <div class="mb-3">
        <label for="state" class="form-label">State *</label>
        <select class="form-select" name="state" required>
          <option value="new" <?php echo $state == 'new' ? 'selected' : ''; ?>>New</option>
          <option value="current" <?php echo $state == 'current' ? 'selected' : ''; ?>>Current</option>
          <option value="maybe" <?php echo $state == 'maybe' ? 'selected' : ''; ?>>Maybe</option>
          <option value="done" <?php echo $state == 'done' ? 'selected' : ''; ?>>Done</option>
        </select>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="mb-3">
        <label for="files_nr" class="form-label">Files Nr *</label>
        <input type="text" class="form-control" name="files_nr" value="<?php echo $filesNr; ?>" 
               pattern="^\d{4}$" title="4 digits with leading zeros" required readonly>
        <div class="form-text">Auto-generated 4-digit number</div>
      </div>
    </div>
  </div>
  
  <div class="mb-3">
    <label for="url" class="form-label">URL</label>
    <input type="url" class="form-control" name="url" value="<?php echo $url; ?>" 
           placeholder="https://example.com">
    <div class="form-text">Optional link to apartment listing</div>
  </div>
  
  <div class="mb-3">
    <label for="result" class="form-label">Result</label>
    <textarea class="form-control" name="result" rows="2"><?php echo $result; ?></textarea>
    <div class="form-text">Info about the outcome or decision</div>
  </div>
  
  <!-- Camera upload section -->
  <div class="mb-3">
    <label class="form-label">Pic</label>
    <div>
      <button type="button" class="btn btn-outline-primary btn-sm" 
              onclick="uploadImage('<?php echo htmlspecialchars($instance['_path'] ?? ''); ?>')">
        📷 Add Pic
      </button>
      <div class="form-text">Use smartphone camera to add images</div>
    </div>
  </div>
</form>
