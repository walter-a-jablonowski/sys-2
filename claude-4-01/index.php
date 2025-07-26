<?php
// Main entry point for the hierarchical data management system

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Get current path from URL parameter
$currentPath = $_GET['path'] ?? '';

// Include core functions
require_once 'core/functions.php';
require_once 'core/type_manager.php';
require_once 'core/data_manager.php';

// Initialize managers
$typeManager = new TypeManager();
$dataManager = new DataManager();

// Get current level data
$currentData = $dataManager->getCurrentLevelData($currentPath);
$currentInstance = $dataManager->getCurrentInstance($currentPath);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
</head>
<body>
  <div class="container-fluid p-0">
    <!-- Header Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
          <?php echo htmlspecialchars($currentData['levelName'] ?? 'Start'); ?>
        </span>
        
        <?php if( $currentPath !== '' ): ?>
          <button class="btn btn-outline-light btn-sm me-2" onclick="navigateBack()">
            ← Back
          </button>
        <?php endif; ?>
        
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
            Actions
          </button>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item <?php echo $currentPath === '' ? 'disabled' : ''; ?>" 
                 href="#" onclick="editCurrentInstance()">
                Edit
              </a>
            </li>
            <li>
              <a class="dropdown-item <?php echo $currentPath === '' ? 'disabled' : ''; ?>" 
                 href="#" onclick="deleteCurrentInstance()">
                Delete
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item disabled" href="#">⚙️ Settings</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Read-only rendering of current entry -->
    <?php if( $currentInstance && $currentPath !== '' ): ?>
      <div id="current-instance-display" class="mx-3">
        <?php echo $dataManager->renderReadOnly($currentInstance); ?>
      </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="container-fluid px-3">
      <!-- Tabs -->
      <ul class="nav nav-tabs mt-3" id="mainTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-pane" type="button" role="tab">
            List
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources-pane" type="button" role="tab">
            Resources
          </button>
        </li>
      </ul>

      <div class="tab-content" id="mainTabContent">
        <!-- List Tab -->
        <div class="tab-pane fade show active" id="list-pane" role="tabpanel">
          <!-- Toolbar -->
          <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
            <select class="form-select w-auto" id="sortSelect" onchange="sortList()">
              <option value="time">Sort by Time</option>
              <option value="name">Sort by Name</option>
            </select>
            <button class="btn btn-primary" onclick="showAddModal()">
              + Add
            </button>
          </div>

          <!-- List -->
          <div class="list-group" id="entryList">
            <?php echo $dataManager->renderList($currentData['entries'] ?? []); ?>
          </div>
        </div>

        <!-- Resources Tab -->
        <div class="tab-pane fade" id="resources-pane" role="tabpanel">
          <div class="mt-3">
            <div class="list-group" id="resourcesList">
              <?php echo $dataManager->renderResources($currentData['resources'] ?? []); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Entry Modal -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addForm">
            <div class="mb-3">
              <label for="entryType" class="form-label">Type</label>
              <select class="form-select" id="entryType" required>
                <?php echo $typeManager->getTypeOptions($currentPath); ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="entryName" class="form-label">Name</label>
              <input type="text" class="form-control" id="entryName" required>
            </div>
            <div class="mb-3">
              <label for="entryDescription" class="form-label">Description</label>
              <textarea class="form-control" id="entryDescription" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="addEntry()">Add</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Entry Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editModalBody">
          <!-- Dynamic content loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveEntry()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Display -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="errorToast" class="toast" role="alert">
      <div class="toast-header bg-danger text-white">
        <strong class="me-auto">Error</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body" id="errorMessage">
        <!-- Error message here -->
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/main.js?v=<?= time() ?>"></script>
</body>
</html>
