<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entry Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="styles.css" rel="stylesheet">
  <link href="types/Activity/styles.css" rel="stylesheet">
  <link href="types/Info/styles.css" rel="stylesheet">
  <link href="types/Apartment/styles.css" rel="stylesheet">
</head>
<body>
  <!-- Header Bar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1" id="currentLevel">Start</span>
      
      <div class="dropdown">
        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#" id="editEntry" style="display: none;"><i class="bi bi-pencil"></i> Edit</a></li>
          <li><a class="dropdown-item" href="#" id="deleteEntry" style="display: none;"><i class="bi bi-trash"></i> Delete</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Content Area -->
  <div class="container-fluid content-area">
    <!-- Current Entry Display -->
    <div id="currentEntryDisplay" style="display: none;">
      <div class="card mb-3">
        <div class="card-body" id="currentEntryContent">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="mainTabs">
      <li class="nav-item">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-pane">List</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources-pane">Resources</button>
      </li>
    </ul>

    <div class="tab-content" id="mainTabContent">
      <!-- List Tab -->
      <div class="tab-pane fade show active" id="list-pane">
        <!-- Toolbar -->
        <div class="d-flex justify-content-between align-items-center my-3">
          <select class="form-select" id="sortBy" style="width: auto;">
            <option value="time">Sort by Time</option>
            <option value="name">Sort by Name</option>
          </select>
          <button class="btn btn-primary" id="addEntry">
            <i class="bi bi-plus"></i> Add
          </button>
        </div>

        <!-- Entry List -->
        <div id="entryList">
          <!-- Entries will be loaded here -->
        </div>
      </div>

      <!-- Resources Tab -->
      <div class="tab-pane fade" id="resources-pane">
        <div id="resourcesList" class="mt-3">
          <!-- Resources will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <!-- Add Entry Modal -->
  <div class="modal fade" id="addEntryModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addEntryForm">
            <div class="mb-3">
              <label for="entryType" class="form-label">Type</label>
              <select class="form-select" id="entryType" required>
                <!-- Options will be loaded dynamically -->
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
          <button type="button" class="btn btn-primary" id="saveNewEntry">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Entry Modal -->
  <div class="modal fade" id="editEntryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editEntryContent">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveEditEntry">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Alert -->
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="errorToast" class="toast" role="alert">
      <div class="toast-header bg-danger text-white">
        <strong class="me-auto">Error</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body" id="errorMessage">
        <!-- Error message will be displayed here -->
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="controller.js"></script>
  <script src="types/Activity/controller.js"></script>
  <script src="types/Info/controller.js"></script>
  <script src="types/Apartment/controller.js"></script>
</body>
</html>
