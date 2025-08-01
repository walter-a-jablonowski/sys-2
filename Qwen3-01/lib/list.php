<?php

/**
 * List items at a path
 */

// Load configuration
$config = loadYaml('config.yml');
$config['dataFileName'] = $config['dataFileName'] ?? '-this';

// Get path from URL parameter
$path = $_GET['path'] ?? '';

// Load type definitions
$types = loadTypeDefinitions();

// Get items at path
$items = getItemsAtPath($path, $config['dataFileName'], $types);

// Sort by time (newest first)
usort($items, function($a, $b) {
  $timeA = isset($a['data']['time']) ? strtotime($a['data']['time']) : 0;
  $timeB = isset($b['data']['time']) ? strtotime($b['data']['time']) : 0;
  return $timeB - $timeA;
});

// Function to get items at a path
function getItemsAtPath($path, $dataFileName, $types) {
  $items = [];
  $fullPath = 'data/' . $path;
  
  if( !file_exists($fullPath) || !is_dir($fullPath) ) {
    return $items;
  }
  
  // Scan directory
  $entries = scandir($fullPath);
  
  foreach( $entries as $entry ) {
    // Skip . and ..
    if( $entry === '.' || $entry === '..' ) {
      continue;
    }
    
    $entryPath = $fullPath . '/' . $entry;
    
    // Check if it's a file or directory
    if( is_file($entryPath) ) {
      // Check if it's a data file
      if( pathinfo($entry, PATHINFO_FILENAME) === $dataFileName && pathinfo($entry, PATHINFO_EXTENSION) === 'md' ) {
        // Parse the data file
        $content = file_get_contents($entryPath);
        $data = parseFrontMatter($content);
        
        // If no type in front matter, try to identify from name
        if( !isset($data['type']) ) {
          $data['type'] = identifyType($entry, $types);
        }
        
        // Extract description from content after front matter
        if( !isset($data['description']) ) {
          $parts = explode("---", $content, 3);
          if( count($parts) >= 3 ) {
            $data['description'] = trim($parts[2]);
          }
        }
        
        $items[] = [
          'name' => $entry,
          'path' => $path . '/' . $entry,
          'type' => $data['type'] ?? 'unknown',
          'data' => $data
        ];
      }
    } elseif( is_dir($entryPath) ) {
      // Check if directory contains a data file
      $dataFile = $entryPath . '/' . $dataFileName . '.md';
      
      if( file_exists($dataFile) ) {
        // Parse the data file
        $content = file_get_contents($dataFile);
        $data = parseFrontMatter($content);
        
        // If no type in front matter, try to identify from name
        if( !isset($data['type']) ) {
          $data['type'] = identifyType($entry, $types);
        }
        
        // Extract description from content after front matter
        if( !isset($data['description']) ) {
          $parts = explode("---", $content, 3);
          if( count($parts) >= 3 ) {
            $data['description'] = trim($parts[2]);
          }
        }
        
        $items[] = [
          'name' => $entry,
          'path' => $path . '/' . $entry,
          'type' => $data['type'] ?? 'unknown',
          'data' => $data
        ];
      }
    }
  }
  
  return $items;
}

// Function to get resource files and group folders at a path
function getResourcesAtPath($path, $dataFileName) {
  $resources = [];
  $fullPath = 'data/' . $path;
  
  if( !file_exists($fullPath) || !is_dir($fullPath) ) {
    return $resources;
  }
  
  // Scan directory
  $entries = scandir($fullPath);
  
  foreach( $entries as $entry ) {
    // Skip . and ..
    if( $entry === '.' || $entry === '..' ) {
      continue;
    }
    
    $entryPath = $fullPath . '/' . $entry;
    
    // Check if it's a file or directory
    if( is_file($entryPath) ) {
      // Check if it's NOT a data file (resource file)
      if( !(pathinfo($entry, PATHINFO_FILENAME) === $dataFileName && pathinfo($entry, PATHINFO_EXTENSION) === 'md') ) {
        $resources[] = [
          'name' => $entry,
          'path' => $path . '/' . $entry,
          'type' => 'resource',
          'size' => filesize($entryPath),
          'modified' => filemtime($entryPath)
        ];
      }
    } elseif( is_dir($entryPath) ) {
      // Check if directory does NOT contain a data file (group folder)
      $dataFile = $entryPath . '/' . $dataFileName . '.md';
      
      if( !file_exists($dataFile) ) {
        $resources[] = [
          'name' => $entry,
          'path' => $path . '/' . $entry,
          'type' => 'group',
          'size' => 0, // Size of directory is not relevant
          'modified' => filemtime($entryPath)
        ];
      }
    }
  }
  
  return $resources;
}

// Function to format file size
function formatFileSize($bytes) {
  if( $bytes < 1024 ) {
    return $bytes . ' B';
  } elseif( $bytes < 1048576 ) {
    return round($bytes / 1024, 2) . ' KB';
  } elseif( $bytes < 1073741824 ) {
    return round($bytes / 1048576, 2) . ' MB';
  } else {
    return round($bytes / 1073741824, 2) . ' GB';
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Manager</title>
  <link rel="stylesheet" href="styles.css">
  <script src="controller.js"></script>
</head>
<body>
  <!-- Header bar -->
  <div class="header-bar d-flex justify-content-between align-items-center">
    <div>
      <?php 
        if( empty($path) ) {
          echo 'Start';
        } else {
          $pathParts = explode('/', trim($path, '/'));
          echo htmlspecialchars(end($pathParts));
        }
      ?>
    </div>
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
      </button>
      <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
        <?php if( !empty($path) ): ?>
          <li><a class="dropdown-item" href="#" onclick="editCurrentEntry()">Edit</a></li>
          <li><a class="dropdown-item" href="#" onclick="deleteCurrentEntry()">Delete</a></li>
        <?php endif; ?>
        <li><a class="dropdown-item" href="#">Settings</a></li>
      </ul>
    </div>
  </div>
  
  <!-- Content area -->
  <div class="content-area">
    <?php if( !empty($path) ): ?>
      <!-- Read only rendering of current entry -->
      <div class="current-entry-details">
        <?php 
          // Find current entry data
          $currentEntry = null;
          $pathParts = explode('/', trim($path, '/'));
          $currentEntryName = end($pathParts);
          
          foreach( $items as $item ) {
            if( $item['name'] === $currentEntryName . '/' . $dataFileName . '.md' || 
                $item['name'] === $currentEntryName ) {
              $currentEntry = $item;
              break;
            }
          }
          
          if( $currentEntry && isset($types[$currentEntry['type']]) ) {
            $type = $currentEntry['type'];
            $data = $currentEntry['data'];
            include "types/{$type}/ready_only.php";
          }
        ?>
      </div>
    <?php endif; ?>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab" aria-controls="list" aria-selected="true">List</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab" aria-controls="resources" aria-selected="false">Resources</button>
      </li>
    </ul>
    
    <div class="tab-content" id="myTabContent">
      <!-- List tab -->
      <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
        <!-- Toolbar -->
        <div class="d-flex justify-content-between align-items-center my-3">
          <div>
            <select class="form-select form-select-sm" id="sortOrder">
              <option value="time">Sort by Time</option>
              <option value="name">Sort by Name</option>
            </select>
          </div>
          <div>
            <button class="btn btn-primary" onclick="showAddModal()">Add</button>
          </div>
        </div>
        
        <!-- List -->
        <div class="list-container">
          <?php foreach( $items as $item ): ?>
            <div class="list-item-card">
              <?php 
                if( isset($types[$item['type']]) ) {
                  $type = $item['type'];
                  $data = $item['data'];
                  include "types/{$type}/list.php";
                }
              ?>
              <div class="action-buttons mt-2">
                <button class="btn btn-sm btn-outline-primary" onclick="editEntry('<?= htmlspecialchars($item['path']) ?>')">Edit</button>
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    ...
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="deleteEntry('<?= htmlspecialchars($item['path']) ?>')">Delete</a></li>
                  </ul>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          
          <?php if( empty($items) ): ?>
            <div class="text-center text-muted mt-3">
              No items found
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Resources tab -->
      <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
        <div class="resources-container">
          <?php
          // Get resources at current path
          $resources = getResourcesAtPath($path, $config['dataFileName']);
          
          if( empty($resources) ): ?>
            <div class="text-center text-muted mt-3">
              No resources found
            </div>
          <?php else: ?>
            <div class="list-group">
              <?php foreach( $resources as $resource ): ?>
                <div class="list-group-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <i class="fas fa-<?php echo $resource['type'] === 'group' ? 'folder' : 'file'; ?> me-2"></i>
                      <?php echo htmlspecialchars($resource['name']); ?>
                    </div>
                    <div class="text-muted small">
                      <?php if( $resource['type'] === 'resource' ): ?>
                        <?php echo formatFileSize($resource['size']); ?>
                      <?php else: ?>
                        Group Folder
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="small text-muted mt-1">
                    Modified: <?php echo date('Y-m-d H:i:s', $resource['modified']); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Add modal -->
  <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Add New Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="entryType" class="form-label">Type</label>
            <select class="form-select" id="entryType">
              <?php foreach( $types as $typeName => $typeDef ): ?>
                <option value="<?= htmlspecialchars($typeName) ?>"><?= htmlspecialchars($typeDef['name'] ?? $typeName) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="entryName" class="form-label">Name</label>
            <input type="text" class="form-control" id="entryName">
          </div>
          <div class="mb-3">
            <label for="entryDescription" class="form-label">Description</label>
            <textarea class="form-control" id="entryDescription" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="addEntry()">Add</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Edit modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Edit Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="editModalBody">
          <!-- Edit form will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveEntry()">Save</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Show add modal
    function showAddModal() {
      const modal = new bootstrap.Modal(document.getElementById('addModal'));
      modal.show();
    }
    
    // Add entry
    function addEntry() {
      const type = document.getElementById('entryType').value;
      const name = document.getElementById('entryName').value;
      const description = document.getElementById('entryDescription').value;
      
      if( !name ) {
        alert('Name is required');
        return;
      }
      
      // Call appropriate type controller
      if( typeof window[`submit${type}Form`] === 'function' ) {
        window[`submit${type}Form`](function(error, result) {
          if( error ) {
            alert('Error: ' + error);
          } else {
            // Reload page
            location.reload();
          }
        });
      } else {
        // Generic save
        const data = {
          type: type,
          name: name,
          description: description,
          time: formatDateTime(new Date()),
          id: generateId(name)
        };
        
        ajaxCall('saveEntry', data, function(error, result) {
          if( error ) {
            alert('Error: ' + error);
          } else {
            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
            modal.hide();
            location.reload();
          }
        });
      }
    }
    
    // Edit entry
    function editEntry(path) {
      // Load edit form for the entry type
      ajaxCall('loadEditForm', { path: path }, function(error, result) {
        if( error ) {
          alert('Error: ' + error);
        } else {
          document.getElementById('editModalBody').innerHTML = result.html;
          const modal = new bootstrap.Modal(document.getElementById('editModal'));
          modal.show();
        }
      });
    }
    
    // Save entry
    function saveEntry() {
      // Get form data
      const form = document.getElementById('editModalBody').querySelector('form');
      if( !form ) {
        alert('No form found');
        return;
      }
      
      const formData = new FormData(form);
      const data = {};
      for( let [key, value] of formData.entries() ) {
        data[key] = value;
      }
      
      // Add time if not present
      if( !data.time ) {
        data.time = formatDateTime(new Date());
      }
      
      // Add ID if not present
      if( !data.id ) {
        data.id = generateId(data.name || 'entry');
      }
      
      // Call appropriate save function based on type
      const type = data.type || 'Entry';
      const action = 'save' + type.charAt(0).toUpperCase() + type.slice(1);
      
      ajaxCall(action, data, function(error, result) {
        if( error ) {
          alert('Error: ' + error);
        } else {
          // Close modal and reload
          const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
          modal.hide();
          location.reload();
        }
      });
    }
    
    // Delete entry
    function deleteEntry(path) {
      if( confirm('Are you sure you want to delete this entry?') ) {
        ajaxCall('deleteEntry', { path: path }, function(error, result) {
          if( error ) {
            alert('Error: ' + error);
          } else {
            // Reload page
            location.reload();
          }
        });
      }
    }
    
    // Edit current entry
    function editCurrentEntry() {
      // Get current path
      const path = '<?php echo htmlspecialchars($path); ?>';
      if( !path ) {
        alert('No current entry to edit');
        return;
      }
      
      // Load edit form for the entry type
      ajaxCall('loadEditForm', { path: path }, function(error, result) {
        if( error ) {
          alert('Error: ' + error);
        } else {
          document.getElementById('editModalBody').innerHTML = result.html;
          const modal = new bootstrap.Modal(document.getElementById('editModal'));
          modal.show();
        }
      });
    }
    
    // Delete current entry
    function deleteCurrentEntry() {
      // Get current path
      const path = '<?php echo htmlspecialchars($path); ?>';
      if( !path ) {
        alert('No current entry to delete');
        return;
      }
      
      if( confirm('Are you sure you want to delete this entry?') ) {
        ajaxCall('deleteEntry', { path: path }, function(error, result) {
          if( error ) {
            alert('Error: ' + error);
          } else {
            // Go back to parent directory
            const pathParts = path.split('/');
            pathParts.pop();
            const parentPath = pathParts.join('/');
            window.location.href = '?path=' + encodeURIComponent(parentPath);
          }
        });
      }
    }
  </script>
</body>
</html>
