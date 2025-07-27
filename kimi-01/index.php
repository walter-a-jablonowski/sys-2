<?php

// Include composer autoloader
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

try
{
  // Load configuration
  $config = Yaml::parseFile('config.yml');
  if (!$config)
  {
    throw new Exception('Failed to load config.yml');
  }

  // Get current path from URL
  $path = isset($_GET['path']) ? $_GET['path'] : '';
  $path = trim($path, '/');
  
  // Prevent directory traversal
  $path = str_replace(array('..', '\\'), array('', '/'), $path);

  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kimi-01 Data Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      body {
        background-color: #f8f9fa;
      }
      .entry-card {
        transition: transform 0.2s;
      }
      .entry-card:hover {
        transform: translateY(-2px);
      }
      .list-cell-content {
        cursor: pointer;
      }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container-fluid">
        <a class="navbar-brand" href="?">Kimi-01</a>
        <?php if ($path): ?>
        <button class="btn btn-outline-light" onclick="history.back()">
          <i class="bi bi-arrow-left"></i> Back
        </button>
        <?php endif; ?>
      </div>
    </nav>

    <div class="container-fluid mt-3">
      <ul class="nav nav-tabs" id="mainTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">
            <i class="bi bi-list"></i> List
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">
            <i class="bi bi-folder"></i> Resources
          </button>
        </li>
      </ul>

      <div class="tab-content" id="mainTabContent">
        <div class="tab-pane fade show active" id="list" role="tabpanel">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <div>
              <select class="form-select form-select-sm" id="sortSelect" style="width: auto;">
                <option value="time">Sort by Time</option>
                <option value="name">Sort by Name</option>
              </select>
            </div>
            <div>
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus"></i> Add
              </button>
            </div>
          </div>
          <div id="entriesList" class="row"></div>
        </div>

        <div class="tab-pane fade" id="resources" role="tabpanel">
          <div class="mt-3">
            <h5>Resources</h5>
            <div id="resourcesList"></div>
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
                <select class="form-select" name="type" required>
                  <option value="">Select type...</option>
                  <option value="Activity">Activity</option>
                  <option value="Info">Info</option>
                  <option value="Apartment">Apartment</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Entry</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="editModalBody">
            <!-- Content loaded dynamically -->
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
  </body>
  </html>
  <?php
}
catch (Exception $e)
{
  ?>
  <div class="alert alert-danger m-3">
    <strong>Error:</strong> <?= htmlspecialchars($e->getMessage()) ?>
  </div>
  <?php
}
?>
