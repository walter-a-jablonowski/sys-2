<?php

require_once __DIR__ . '/vendor/autoload.php';

$system = new App\System();

$current_path = $_GET['path'] ?? ''; // Basic navigation

// Determine allowed types for the 'Add' modal
$creatable_types = [];
$current_entry_for_types = $system->getEntryByPath(str_replace('/', '\\', $current_path));

if ($current_entry_for_types && $current_entry_for_types['type']) {
    $type_def = $system->getType($current_entry_for_types['type']);
    $allowed_sub_types = $type_def['allowedSubTypes'] ?? [];
    if ($allowed_sub_types === ['*']) {
        $all_types = $system->getTypes();
        unset($all_types['global']);
        $creatable_types = $all_types;
    } else {
        foreach ($allowed_sub_types as $type_id) {
            $creatable_types[$type_id] = $system->getType($type_id);
        }
    }
} else if (empty($current_path)) {
    // At root, allow all types
    $all_types = $system->getTypes();
    unset($all_types['global']);
    $creatable_types = $all_types;
}

$data = $system->getData($current_path);

$list_items = [];
$resource_items = [];
foreach ($data as $item) {
    if ($item['type']) {
        $list_items[] = $item;
    } else {
        $resource_items[] = $item;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body {
      padding-top: 56px; /* Adjust for fixed-top navbar */
    }
    .content-area {
      height: calc(100vh - 56px);
      overflow-y: auto;
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="#" id="header-title">Start</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Actions
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item disabled" href="#" id="edit-instance">Edit</a></li>
              <li><a class="dropdown-item disabled" href="#" id="delete-instance">Delete</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid content-area">
    <div id="read-only-view" class="container pt-3" style="display: none;">
      <!-- Read only view will be rendered here -->
    </div>

    <ul class="nav nav-tabs" id="main-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-content" type="button" role="tab" aria-controls="list-content" aria-selected="true">List</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources-content" type="button" role="tab" aria-controls="resources-content" aria-selected="false">Resources</button>
      </li>
    </ul>

    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="list-content" role="tabpanel" aria-labelledby="list-tab">
        <div class="d-flex justify-content-between align-items-center p-2 bg-light">
          <div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="sort-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
              Sort by: Time
            </button>
            <ul class="dropdown-menu" aria-labelledby="sort-dropdown">
              <li><a class="dropdown-item" href="#">Time</a></li>
              <li><a class="dropdown-item" href="#">Name</a></li>
            </ul>
          </div>
          <button class="btn btn-primary btn-sm" id="add-button" data-bs-toggle="modal" data-bs-target="#add-entry-modal" <?php if (empty($creatable_types)) echo 'disabled'; ?>>Add</button>
        </div>
        <div class="container-fluid mt-5 pt-3">
  <?php
    $isSingleEntryView = false;
    if ($current_path && is_dir('data/' . $current_path)) {
        $dataFileName = $system->getConfig()['dataFileName'] ?? '-this';
        $entryFilePath = 'data/' . $current_path . '/' . $dataFileName . '.md';
        if (file_exists($entryFilePath)) {
            $isSingleEntryView = true;
        }
    }

    if ($isSingleEntryView) {
        $entry_data = $system->parseEntry('data/' . $current_path);
        if ($entry_data) {
            $renderer_path = $system->getRendererPath($entry_data['type'], 'read_only.php');
            if ($renderer_path) {
                include $renderer_path;
            } else {
                echo '<div class="alert alert-warning">No read-only view available for this type.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Could not load entry data.</div>';
        }
    } else {
  ?>
    <ul class="nav nav-tabs" id="main-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-content" type="button" role="tab" aria-controls="list-content" aria-selected="true">List</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources-content" type="button" role="tab" aria-controls="resources-content" aria-selected="false">Resources</button>
      </li>
    </ul>
    <div class="tab-content" id="main-tabs-content">
      <div class="tab-pane fade show active" id="list-content" role="tabpanel" aria-labelledby="list-tab">
        <div id="list-container" class="list-group">
          <?php if (empty($list_items)): ?>
            <div class="alert alert-info mt-2">No items found.</div>
          <?php else: ?>
            <?php foreach ($list_items as $entry): ?>
              <?php
                $renderer_path = $system->getRendererPath($entry['type'], 'list.php');
                if ($renderer_path) {
                  echo '<div class="list-group-item list-group-item-action" data-path="' . htmlspecialchars($entry['path']) . '">';
                  $entry_data = $entry;
                  include $renderer_path;
                  echo '</div>';
                } else {
                  echo '<div class="list-group-item">' . htmlspecialchars($entry['name']) . ' (Unknown Type)</div>';
                }
              ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="tab-pane fade" id="resources-content" role="tabpanel" aria-labelledby="resources-tab">
        <div id="resource-container" class="list-group">
          <?php if (empty($resource_folders)): ?>
            <div class="alert alert-info mt-2">No resources found.</div>
          <?php else: ?>
            <?php foreach ($resource_folders as $item): ?>
              <div class="list-group-item list-group-item-action" data-path="<?= htmlspecialchars($item['path']) ?>">
                <i class="bi bi-folder"></i> <?= htmlspecialchars($item['name']) ?>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php
    } // end else for isSingleEntryView
  ?>
</div>
      </div>
      <div class="tab-pane fade" id="resources-content" role="tabpanel" aria-labelledby="resources-tab">
        <div id="resources-container" class="list-group list-group-flush">
          <?php foreach ($resource_items as $item): ?>
            <div class="list-group-item" data-path="<?php echo htmlspecialchars($item['path']); ?>">
              <i class="bi <?php echo $item['is_dir'] ? 'bi-folder' : 'bi-file-earmark'; ?>"></i>
              <?php echo htmlspecialchars($item['name']); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  const creatableTypes = <?php echo json_encode(array_values($creatable_types)); ?>;
</script>
<script src="js/main.js?v=<?= time() ?>"></script>

  <!-- Add Entry Modal -->
  <div class="modal fade" id="addEntryModal" tabindex="-1" aria-labelledby="addEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addEntryModalLabel">Add New Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="add-entry-form">
            <div class="mb-3">
              <label for="entry-type" class="form-label">Type</label>
              <select class="form-select" id="entry-type" name="type" required>
                <?php foreach ($creatable_types as $type): ?>
                  <option value="<?php echo htmlspecialchars($type['id']); ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="entry-name" class="form-label">Name</label>
              <input type="text" class="form-control" id="entry-name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="entry-description" class="form-label">Description</label>
              <textarea class="form-control" id="entry-description" name="description" rows="3"></textarea>
            </div>
            <div id="type-specific-fields-add" class="mb-3"></div>
            <div class="mb-3" id="image-upload-container-add" style="display: none;">
              <label for="images-add" class="form-label">Images</label>
              <input class="form-control" type="file" id="images-add" name="images[]" multiple>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" form="add-entry-form" class="btn btn-primary">Add Entry</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Entry Modal -->
  <div class="modal fade" id="editEntryModal" tabindex="-1" aria-labelledby="editEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editEntryModalLabel">Edit Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editEntryForm">
            <input type="hidden" id="editEntryPath" name="path">
            <div class="mb-3">
              <label for="editEntryName" class="form-label">Name</label>
              <input type="text" class="form-control" id="editEntryName" name="name" required>
            </div>
            <div class="mb-3">
              <label for="editEntryDescription" class="form-label">Description</label>
              <textarea class="form-control" id="editEntryDescription" name="description" rows="3"></textarea>
            </div>
            <div id="type-specific-fields-edit" class="mb-3"></div>
            <div class="mb-3" id="image-upload-container-edit" style="display: none;">
              <label for="images-edit" class="form-label">Images</label>
              <input class="form-control" type="file" id="images-edit" name="images[]" multiple>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveEditButton">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
