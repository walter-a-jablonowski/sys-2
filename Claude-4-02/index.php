<?php
require_once 'classes/TypeManager.php';
require_once 'classes/InstanceManager.php';

$typeManager = new TypeManager();
$instanceManager = new InstanceManager();

// Get current path from URL parameter
$currentPath = $_GET['path'] ?? 'data';
$currentPath = ltrim($currentPath, '/');

// Ensure data directory exists
if( ! is_dir('data') ) {
  mkdir('data', 0755, true);
}

// Load current instance data if we're in a specific instance
$currentInstance = null;
$currentType = null;
$levelName = 'Start';

if( $currentPath !== 'data' ) {
  $currentInstance = $instanceManager->loadInstance($currentPath);
  if( $currentInstance ) {
    $currentType = $currentInstance['type'] ?? null;
    $levelName = $currentInstance['name'] ?? basename($currentPath);
  }
}

// Get list of instances and resources
$listData = $instanceManager->listInstances($currentPath);
$instances = $listData['instances'];
$resources = $listData['resources'];

// Get allowed sub types for current level
$allowedSubTypes = [];
if( $currentType ) {
  $allowedSubTypes = $typeManager->getAllowedSubTypes($currentType);
} else {
  $allowedSubTypes = array_keys($typeManager->getAllTypes());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entry Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      padding-top: 70px;
    }
    .header-bar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: white;
      border-bottom: 1px solid #dee2e6;
    }
    .content-area {
      height: calc(100vh - 70px);
      overflow-y: auto;
    }
    .instance-card {
      cursor: pointer;
      transition: all 0.2s;
    }
    .instance-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card-actions {
      opacity: 0.7;
    }
    .card-actions:hover {
      opacity: 1;
    }
  </style>
</head>
<body>
  <!-- Header Bar -->
  <div class="header-bar">
    <nav class="navbar navbar-expand-lg navbar-light bg-light px-3">
      <span class="navbar-brand mb-0 h1"><?= htmlspecialchars($levelName) ?></span>
      
      <div class="dropdown ms-auto">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if( $currentPath !== 'data' ): ?>
            <li><a class="dropdown-item" href="#" onclick="editCurrentInstance()">
              <i class="bi bi-pencil"></i> Edit
            </a></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="deleteCurrentInstance()">
              <i class="bi bi-trash"></i> Delete
            </a></li>
            <li><hr class="dropdown-divider"></li>
          <?php endif; ?>
          <li><a class="dropdown-item" href="#" onclick="showSettings()">
            <i class="bi bi-gear"></i> Settings
          </a></li>
        </ul>
      </div>
    </nav>
  </div>

  <!-- Content Area -->
  <div class="content-area">
    <div class="container-fluid p-3">
      
      <!-- Current Instance Display -->
      <?php if( $currentInstance && $currentType ): ?>
        <div class="card mb-3">
          <div class="card-body">
            <?php
            $readOnlyFile = "types/$currentType/read_only.php";
            if( file_exists($readOnlyFile) ) {
              include $readOnlyFile;
            } else {
              // Default read-only display
              echo "<h5>" . htmlspecialchars($currentInstance['name']) . "</h5>";
              if( isset($currentInstance['description']) ) {
                echo "<p>" . nl2br(htmlspecialchars($currentInstance['description'])) . "</p>";
              }
            }
            ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Tabs -->
      <ul class="nav nav-tabs" id="mainTabs" role="tablist">
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

      <div class="tab-content" id="mainTabsContent">
        <!-- List Tab -->
        <div class="tab-pane fade show active" id="list-pane" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center my-3">
            <select class="form-select w-auto" id="sortSelect" onchange="sortInstances()">
              <option value="time">Sort by Time</option>
              <option value="name">Sort by Name</option>
            </select>
            <button class="btn btn-primary" onclick="showAddModal()">
              <i class="bi bi-plus"></i> Add
            </button>
          </div>

          <div id="instancesList">
            <?php foreach( $instances as $instance ): ?>
              <div class="card instance-card mb-2" onclick="navigateToInstance('<?= htmlspecialchars($instance['path']) ?>')">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <?php
                      $listFile = "types/{$instance['type']}/list.php";
                      if( file_exists($listFile) ) {
                        $instanceData = $instance['data'];
                        include $listFile;
                      } else {
                        // Default list display
                        echo "<strong>" . htmlspecialchars($instance['data']['name']) . "</strong><br>";
                        echo "<small class='text-muted'>" . htmlspecialchars($instance['data']['time']) . "</small>";
                      }
                      ?>
                    </div>
                    <div class="card-actions" onclick="event.stopPropagation()">
                      <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" onclick="editInstance('<?= htmlspecialchars($instance['path']) ?>')">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteInstance('<?= htmlspecialchars($instance['path']) ?>')">
                              <i class="bi bi-trash"></i> Delete
                            </a></li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Resources Tab -->
        <div class="tab-pane fade" id="resources-pane" role="tabpanel">
          <div class="my-3">
            <?php if( empty($resources) ): ?>
              <p class="text-muted">No resource files or folders found.</p>
            <?php else: ?>
              <?php foreach( $resources as $resource ): ?>
                <div class="card mb-2">
                  <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-<?= $resource['type'] === 'folder' ? 'folder' : 'file-earmark' ?> me-2"></i>
                      <span class="flex-grow-1"><?= htmlspecialchars($resource['name']) ?></span>
                      <?php if( $resource['type'] === 'file' && isset($resource['size']) ): ?>
                        <small class="text-muted"><?= number_format($resource['size'] / 1024, 1) ?> KB</small>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Modal -->
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
              <label class="form-label">Type</label>
              <select class="form-select" id="addType" required>
                <option value="">Select type...</option>
                <?php foreach( $allowedSubTypes as $typeName ): ?>
                  <?php $type = $typeManager->getType($typeName); ?>
                  <option value="<?= htmlspecialchars($typeName) ?>"><?= htmlspecialchars($type['name'] ?? $typeName) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" id="addName" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" id="addDescription" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="addInstance()">Add</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editModalBody">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveInstance()">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentPath = '<?= htmlspecialchars($currentPath) ?>';
    let currentEditPath = '';

    function navigateToInstance( path ) {
      window.location.href = `?path=${encodeURIComponent(path)}`;
    }

    function showAddModal() {
      new bootstrap.Modal(document.getElementById('addModal')).show();
    }

    function addInstance() {
      const form = document.getElementById('addForm');
      const formData = new FormData(form);
      
      const data = {
        action: 'add_instance',
        path: currentPath,
        type: document.getElementById('addType').value,
        name: document.getElementById('addName').value,
        description: document.getElementById('addDescription').value
      };

      fetch('ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then( response => response.json() )
      .then( result => {
        if( result.success ) {
          location.reload();
        } else {
          alert('Error: ' + result.message);
        }
      })
      .catch( error => {
        console.error('Error:', error);
        alert('An error occurred while adding the instance.');
      });
    }

    function editInstance( path ) {
      currentEditPath = path;
      
      fetch('ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_edit_form', path: path })
      })
      .then( response => response.json() )
      .then( result => {
        if( result.success ) {
          document.getElementById('editModalBody').innerHTML = result.html;
          new bootstrap.Modal(document.getElementById('editModal')).show();
        } else {
          alert('Error: ' + result.message);
        }
      });
    }

    function saveInstance() {
      const form = document.getElementById('editForm');
      const formData = new FormData(form);
      
      const data = { action: 'save_instance', path: currentEditPath };
      for( let [key, value] of formData.entries() ) {
        data[key] = value;
      }

      fetch('ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then( response => response.json() )
      .then( result => {
        if( result.success ) {
          location.reload();
        } else {
          alert('Error: ' + result.message);
        }
      });
    }

    function deleteInstance( path ) {
      if( confirm('Are you sure you want to delete this entry?') ) {
        fetch('ajax.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'delete_instance', path: path })
        })
        .then( response => response.json() )
        .then( result => {
          if( result.success ) {
            location.reload();
          } else {
            alert('Error: ' + result.message);
          }
        });
      }
    }

    function editCurrentInstance() {
      if( currentPath !== 'data' ) {
        editInstance(currentPath);
      }
    }

    function deleteCurrentInstance() {
      if( currentPath !== 'data' && confirm('Are you sure you want to delete this entry?') ) {
        fetch('ajax.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'delete_instance', path: currentPath })
        })
        .then( response => response.json() )
        .then( result => {
          if( result.success ) {
            // Navigate back to parent
            const parentPath = currentPath.split('/').slice(0, -1).join('/') || 'data';
            window.location.href = `?path=${encodeURIComponent(parentPath)}`;
          } else {
            alert('Error: ' + result.message);
          }
        });
      }
    }

    function showSettings() {
      alert('Settings functionality not implemented yet.');
    }

    function sortInstances() {
      // This would be implemented to sort the current list
      // For now, we'll just reload with a sort parameter
      const sortBy = document.getElementById('sortSelect').value;
      const url = new URL(window.location);
      url.searchParams.set('sort', sortBy);
      window.location.href = url.toString();
    }
  </script>
</body>
</html>
