<form id="editForm">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($instanceData['name'] ?? '') ?>" required>
  </div>
  
  <div class="mb-3">
    <label class="form-label">State</label>
    <select class="form-select" name="state" required>
      <option value="new" <?= ($instanceData['state'] ?? 'new') === 'new' ? 'selected' : '' ?>>New</option>
      <option value="current" <?= ($instanceData['state'] ?? 'new') === 'current' ? 'selected' : '' ?>>Current</option>
      <option value="maybe" <?= ($instanceData['state'] ?? 'new') === 'maybe' ? 'selected' : '' ?>>Maybe</option>
      <option value="done" <?= ($instanceData['state'] ?? 'new') === 'done' ? 'selected' : '' ?>>Done</option>
    </select>
  </div>
  
  <div class="mb-3">
    <label class="form-label">URL</label>
    <input type="url" class="form-control" name="url" value="<?= htmlspecialchars($instanceData['url'] ?? '') ?>" placeholder="https://...">
  </div>
  
  <div class="mb-3">
    <label class="form-label">Files Nr</label>
    <input type="text" class="form-control" name="files_nr" value="<?= htmlspecialchars($instanceData['files_nr'] ?? '') ?>" pattern="\d{4}" placeholder="0001" readonly>
    <div class="form-text">This is automatically generated and cannot be changed.</div>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Result</label>
    <input type="text" class="form-control" name="result" value="<?= htmlspecialchars($instanceData['result'] ?? '') ?>">
  </div>
  
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($instanceData['description'] ?? '') ?></textarea>
  </div>
  
  <div class="mb-3">
    <label class="form-label">Time</label>
    <input type="text" class="form-control" name="time" value="<?= htmlspecialchars($instanceData['time'] ?? '') ?>" pattern="\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}" placeholder="YYYY-MM-DD HH:MM:SS">
  </div>
  
  <!-- Camera Upload Section -->
  <div class="mb-3">
    <label class="form-label">Add Images</label>
    <div class="d-grid">
      <button type="button" class="btn btn-outline-primary" onclick="captureImage()">
        <i class="bi bi-camera"></i> Take Photo
      </button>
    </div>
    <input type="file" id="imageInput" accept="image/*" capture="environment" style="display: none;" onchange="uploadImage(this)">
  </div>
  
  <input type="hidden" name="id" value="<?= htmlspecialchars($instanceData['id'] ?? '') ?>">
  <input type="hidden" name="type" value="<?= htmlspecialchars($instanceData['type'] ?? '') ?>">
</form>

<script>
function captureImage() {
  document.getElementById('imageInput').click();
}

function uploadImage( input ) {
  if( input.files && input.files[0] ) {
    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('apartment_path', currentEditPath);

    fetch('ajax.php', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then( response => response.json() )
    .then( result => {
      if( result.success ) {
        alert('Image uploaded successfully: ' + result.filename);
      } else {
        alert('Error uploading image: ' + result.message);
      }
    })
    .catch( error => {
      console.error('Error:', error);
      alert('An error occurred while uploading the image.');
    });
  }
}
</script>
