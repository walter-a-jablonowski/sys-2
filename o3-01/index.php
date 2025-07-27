<?php

require 'core/bootstrap.php';
require 'core/helpers.php';

[$globalDef, $types] = list_types();

$path = $_GET['path'] ?? '';
$currentPath = DATA_DIR . ($path ? '/' . trim($path, '/') : '');

// -------------------------------------------------
// Helpers to list directory entries and resources
// -------------------------------------------------

function detect_type( string $name, array $types ) : ?string
{
  foreach( $types as $id => $info ) {
    if( ! empty($info['typeIdentification']) && preg_match('/' . $info['typeIdentification'] . '/i', $name) ) {
      return $id;
    }
  }
  return null;
}

function dir_entries( string $dir, array $types ) : array
{
  $entries = scandir($dir);
  $items = [];
  foreach( $entries as $e ) {
    if( $e === '.' || $e === '..' ) continue;
    $full = "$dir/$e";
    $isDir = is_dir($full);

    // resource or group folder detection will be done client side
    $type = null;
    if( $isDir ) {
      // folder instance if contains dataFileName.md
      $cfg = app_config();
      $dataFile = "$full/{$cfg['dataFileName']}.md";
      if( file_exists($dataFile) ) {
        // read front matter to identify type
        $raw = file_get_contents($dataFile);
        [$yaml,] = parse_front_matter($raw);
        if( ! empty($yaml['type']) ) {
          $type = $yaml['type'];
        }
      }
      else {
        $type = detect_type($e, $types);
      }
    }
    else {
      $type = detect_type($e, $types);
    }
    $items[] = [ 'name' => $e, 'isDir' => $isDir, 'type' => $type ];
  }
  return $items;
}

$items = dir_entries($currentPath, $types);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <title>Hierarchy App</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='d-flex flex-column h-100'>

<nav class='navbar navbar-light bg-light fixed-top shadow-sm'>
  <div class='container-fluid'>
    <span class='navbar-brand mb-0 h6'><?= $path ? htmlspecialchars(basename($path)) : 'Start' ?></span>
    <div class='dropdown'>
      <button class='btn btn-link' data-bs-toggle='dropdown'><i class='bi bi-gear'></i></button>
      <ul class='dropdown-menu dropdown-menu-end small'>
        <li><a class='dropdown-item disabled'>Settings</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class='flex-grow-1 pt-5'>
  <div class='container-fluid'>
    <ul class='nav nav-tabs' id='mainTabs' role='tablist'>
      <li class='nav-item' role='presentation'>
        <button class='nav-link active' data-bs-toggle='tab' data-bs-target='#tabList' type='button'>List</button>
      </li>
      <li class='nav-item' role='presentation'>
        <button class='nav-link' data-bs-toggle='tab' data-bs-target='#tabRes' type='button'>Resources</button>
      </li>
    </ul>
    <div class='tab-content'>
      <div class='tab-pane fade show active' id='tabList'>
        <div class='d-flex justify-content-between my-2'>
          <select class='form-select w-auto form-select-sm' id='sortSel'>
            <option value='time'>By Time</option>
            <option value='name'>By Name</option>
          </select>
          <button class='btn btn-sm btn-primary' onclick='showAddModal()'>Add</button>
        </div>
        <div id='listArea'>
          <?php foreach( $items as $it ) :
            $itemPath = $currentPath . '/' . $it['name']; ?>
            <?php if( $it['type'] && isset($types[$it['type']]) ) : ?>
              <?php include $types[$it['type']]['_dir'] . '/list.php'; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <div class='tab-pane fade' id='tabRes'>Resources TBD</div>
    </div>
  </div>
</main>

<!-- Add Modal -->
<div class='modal fade' id='addModal' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Add Entry</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
      </div>
      <div class='modal-body'>
        <div class='mb-2'>
          <label class='form-label small'>Type</label>
          <select id='addType' class='form-select form-select-sm'>
            <?php foreach($types as $tid=>$tinfo): ?>
              <option value='<?= $tid ?>'><?= htmlspecialchars($tid) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class='mb-2'>
          <label class='form-label small'>Name</label>
          <input id='addName' class='form-control form-control-sm'>
        </div>
        <div class='mb-2'>
          <label class='form-label small'>Description</label>
          <textarea id='addDesc' class='form-control form-control-sm'></textarea>
        </div>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-secondary btn-sm' data-bs-dismiss='modal'>Cancel</button>
        <button class='btn btn-primary btn-sm' onclick='submitAdd("<?= htmlspecialchars($path) ?>")'>Save</button>
      </div>
    </div>
  </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js'></script>
<script src='main.js'></script>
</body>
</html>
