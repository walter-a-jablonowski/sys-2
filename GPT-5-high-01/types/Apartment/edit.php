<?php /* $d, $is_new, $type_id */ ?>
<form id="type-edit-form">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars((string)($d['name'] ?? '')) ?>" required>
  </div>
  <div class="row g-2">
    <div class="col-6">
      <label class="form-label">Priority</label>
      <select class="form-select" name="priority">
        <?php for( $i = 1; $i <= 5; $i++ ) : ?>
          <option value="<?= $i ?>" <?php if( (string)($d['priority'] ?? '3') === (string)$i ) : ?>selected<?php endif ?>><?= $i ?></option>
        <?php endfor ?>
      </select>
    </div>
    <div class="col-6">
      <label class="form-label">State</label>
      <select class="form-select" name="state">
        <?php $states = ['new','current','maybe','done']; foreach( $states as $s ) : ?>
          <option value="<?= $s ?>" <?php if( (string)($d['state'] ?? 'new') === $s ) : ?>selected<?php endif ?>><?= $s ?></option>
        <?php endforeach ?>
      </select>
    </div>
  </div>
  <div class="mt-3">
    <label class="form-label">URL</label>
    <input type="url" class="form-control" name="url" value="<?= htmlspecialchars((string)($d['url'] ?? '')) ?>" placeholder="https://...">
  </div>
  <div class="mt-3">
    <label class="form-label">Files Nr</label>
    <div class="input-group">
      <input type="text" class="form-control" name="files_nr" value="<?= htmlspecialchars((string)($d['files_nr'] ?? '')) ?>" placeholder="0000" <?php if( $is_new ) : ?>readonly<?php endif ?>>
      <?php if( $is_new ) : ?>
        <button type="button" class="btn btn-outline-secondary" id="btn-get-files-nr">Get next</button>
      <?php endif ?>
    </div>
    <?php if( ! $is_new ) : ?>
      <div class="form-text">Files Nr is fixed after creation.</div>
    <?php endif ?>
  </div>
  <div class="mt-3">
    <label class="form-label">Result</label>
    <input type="text" class="form-control" name="result" value="<?= htmlspecialchars((string)($d['result'] ?? '')) ?>">
  </div>
  <div class="mt-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" rows="5" name="description"><?= htmlspecialchars((string)($d['description'] ?? '')) ?></textarea>
  </div>
  <?php if( ! $is_new ) : ?>
    <hr>
    <div class="mt-2">
      <label class="form-label">Add images</label>
      <div class="d-flex align-items-center gap-2">
        <input type="file" id="apt-image-input" accept="image/*" capture="environment" class="form-control">
        <button type="button" id="apt-upload-btn" class="btn btn-outline-primary">Upload</button>
      </div>
      <div class="form-text">Accepted types: jpg, jpeg, png, webp, heic/heif.</div>
    </div>
  <?php endif ?>
</form>
