<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hierarchical Data Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
  </head>
  <body>
    <header class="navbar navbar-dark bg-dark fixed-top shadow-sm">
      <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Data Manager</span>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-light btn-sm" id="btn-back">Back</button>
          <div class="btn-group">
            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" id="btn-add">
              Add
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="add-type-menu">
              <!-- filled by JS -->
            </ul>
          </div>
          <button class="btn btn-outline-light btn-sm" id="btn-edit">Edit</button>
          <button class="btn btn-outline-danger btn-sm" id="btn-delete">Delete</button>
          <button class="btn btn-outline-light btn-sm" id="btn-refresh">Refresh</button>
        </div>
      </div>
    </header>

    <main class="container mt-4 pt-4">
      <div class="small text-muted mb-2" id="breadcrumb">/</div>

      <div id="read-only" class="mb-3"></div>

      <ul class="nav nav-tabs" id="main-tabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-list-tab" data-bs-toggle="tab" data-bs-target="#tab-list" type="button" role="tab">List</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-res-tab" data-bs-toggle="tab" data-bs-target="#tab-res" type="button" role="tab">Resources</button>
        </li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-list" role="tabpanel">
          <div id="list-items" class="list-group list-group-flush"></div>
        </div>
        <div class="tab-pane fade" id="tab-res" role="tabpanel">
          <div id="resource-items" class="list-group list-group-flush"></div>
        </div>
      </div>
    </main>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalTitle">Edit</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="editModalBody">
            <!-- form injected here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="btn-save">Save</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="controller.js"></script>
  </body>
</html>
