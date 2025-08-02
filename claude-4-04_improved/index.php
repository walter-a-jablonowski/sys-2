<?php
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// Load configuration
$config = Yaml::parseFile('config.yml');

// Get current path from URL parameter
$path = $_GET['path'] ?? '';
$path = trim($path, '/');

// Initialize data manager
require_once 'lib/DataManager.php';
$dataManager = new DataManager();

// Get current level data
$currentLevel = $dataManager->getCurrentLevel($path);
$entries = $dataManager->getEntries($path);
$resources = $dataManager->getResourcesAtPath($path);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hierarchical Data Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Fixed Header Bar -->
  <nav class="navbar navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1"><?= htmlspecialchars($currentLevel['name'] ?? 'Home') ?></span>
      
      <?php if( $currentLevel ): ?>
      <div class="dropdown">
        <button class="btn btn-outline-light" type="button" data-bs-toggle="dropdown">
          ⋮
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#" onclick="editEntry('<?= htmlspecialchars($path) ?>')">Edit</a></li>
          <li><a class="dropdown-item" href="#" onclick="deleteEntry('<?= htmlspecialchars($path) ?>')">Delete</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#">⚙️ Settings</a></li>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Content Area -->
  <div class="container-fluid content-area">
    <?php if( $currentLevel ): ?>
    <!-- Read-only instance display -->
    <div class="card mb-3">
      <div class="card-body">
        <?php
        $typeRenderer = $dataManager->getTypeRenderer($currentLevel['type'], 'read_only');
        if( $typeRenderer )
        {
          $entry = $currentLevel; // Set entry variable for type renderer
          include $typeRenderer;
        }
        else
        {
          echo '<h5>' . htmlspecialchars($currentLevel['name']) . '</h5>';
          if( $currentLevel['description'] )
            echo '<p>' . nl2br(htmlspecialchars($currentLevel['description'])) . '</p>';
        }
        ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tabbed Interface -->
    <ul class="nav nav-tabs" id="mainTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">
          List
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">
          Resources
        </button>
      </li>
    </ul>

    <div class="tab-content" id="mainTabContent">
      <!-- List Tab -->
      <div class="tab-pane fade show active" id="list" role="tabpanel">
        <!-- Toolbar -->
        <div class="d-flex justify-content-between align-items-center my-3">
          <select class="form-select w-auto" id="sortBy">
            <option value="time">Sort by Time</option>
            <option value="name">Sort by Name</option>
          </select>
          <button class="btn btn-primary" onclick="showAddModal()">Add</button>
        </div>

        <!-- Entry List -->
        <div id="entryList">
          <?php foreach( $entries as $entry ): ?>
          <div class="card mb-2 entry-card" data-path="<?= htmlspecialchars($entry['path']) ?>">
            <div class="card-body">
              <?php
              $typeRenderer = $dataManager->getTypeRenderer($entry['type'], 'list');
              if( $typeRenderer )
              {
                include $typeRenderer;
              }
              else
              {
                echo '<div class="d-flex justify-content-between">';
                echo '<span>' . htmlspecialchars($entry['name']) . '</span>';
                echo '<small class="text-muted">' . htmlspecialchars($entry['time']) . '</small>';
                echo '</div>';
              }
              ?>
              
              <div class="dropdown float-end">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">⋮</button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#" onclick="editEntry('<?= htmlspecialchars($entry['path']) ?>')">Edit</a></li>
                  <li><a class="dropdown-item" href="#" onclick="deleteEntry('<?= htmlspecialchars($entry['path']) ?>')">Delete</a></li>
                </ul>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Resources Tab -->
      <div class="tab-pane fade" id="resources" role="tabpanel">
        <div class="my-3">
          <?php foreach( $resources as $resource ): ?>
          <div class="card mb-2">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="me-2"><?= $resource['icon'] ?></span>
                  <span><?= htmlspecialchars($resource['name']) ?></span>
                </div>
                <div class="text-end">
                  <?php if( $resource['type'] === 'file' ): ?>
                  <small class="text-muted d-block"><?= $resource['size'] ?></small>
                  <?php endif; ?>
                  <small class="text-muted"><?= $resource['modified'] ?></small>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Entry Modal -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="typeSelection">
            <p>Select entry type:</p>
            <div id="typeButtons"></div>
          </div>
          <div id="entryForm" style="display: none;"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Entry Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="editForm">
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this entry?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="controller.js"></script>
  
  <script>
    // Initialize current path
    window.currentPath = '<?= htmlspecialchars($path) ?>';
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      initializePage();
    });
  </script>
</body>
</html>
