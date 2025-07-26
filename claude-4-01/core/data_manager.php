<?php
// Data management class

class DataManager
{
  private $typeManager;
  
  public function __construct()
  {
    $this->typeManager = new TypeManager();
  }
  
  /**
   * Get current level data (entries and resources)
   */
  public function getCurrentLevelData( $path )
  {
    $dataPath = empty($path) ? 'data' : "data/$path";
    
    if( !is_dir($dataPath) ) {
      return [
        'levelName' => empty($path) ? 'Start' : basename($path),
        'entries' => [],
        'resources' => []
      ];
    }
    
    $entries = [];
    $resources = [];
    $items = scandir($dataPath);
    
    foreach( $items as $item ) {
      if( $item === '.' || $item === '..' ) {
        continue;
      }
      
      $itemPath = "$dataPath/$item";
      
      // Check if it's a typed entry
      $type = $this->typeManager->identifyTypeFromPath($itemPath);
      
      if( $type ) {
        // It's a typed entry
        $instance = $this->getInstanceFromPath($itemPath);
        if( $instance ) {
          $instance['_path'] = empty($path) ? $item : "$path/$item";
          $instance['_type'] = $type;
          $entries[] = $instance;
        }
      } else {
        // It's a resource file or group folder
        $resources[] = [
          'name' => $item,
          'path' => $itemPath,
          'is_dir' => is_dir($itemPath),
          'size' => is_file($itemPath) ? filesize($itemPath) : 0
        ];
      }
    }
    
    return [
      'levelName' => empty($path) ? 'Start' : basename($path),
      'entries' => $entries,
      'resources' => $resources
    ];
  }
  
  /**
   * Get instance data from path
   */
  private function getInstanceFromPath( $itemPath )
  {
    if( is_dir($itemPath) ) {
      $dataFile = "$itemPath/-this.md";
    } else {
      $dataFile = $itemPath;
    }
    
    if( !file_exists($dataFile) ) {
      return null;
    }
    
    $content = file_get_contents($dataFile);
    $parsed = parseFrontMatter($content);
    
    $instance = $parsed['data'];
    $instance['description'] = $parsed['content'];
    
    return $instance;
  }
  
  /**
   * Get current instance (for read-only display)
   */
  public function getCurrentInstance( $path )
  {
    if( empty($path) ) {
      return null;
    }
    
    return $this->getInstance($path);
  }
  
  /**
   * Get instance by path
   */
  public function getInstance( $path )
  {
    $dataPath = "data/$path";
    return $this->getInstanceFromPath($dataPath);
  }
  
  /**
   * Create new entry
   */
  public function createEntry( $type, $name, $description, $parentPath )
  {
    // Generate ID
    $id = generateId($name);
    
    // Create entry data
    $defaultFields = $this->typeManager->getDefaultFields();
    $typeFields = $this->typeManager->getTypeFields($type);
    
    $entryData = $defaultFields;
    $entryData['id'] = $id;
    $entryData['name'] = $name;
    $entryData['type'] = $type;
    
    // Add default values for type-specific fields
    foreach( $typeFields as $fieldName => $fieldDef ) {
      if( isset($fieldDef['values']) && is_array($fieldDef['values']) ) {
        // For dropdown fields, use first value as default
        $entryData[$fieldName] = array_values($fieldDef['values'])[0] ?? '';
      } else {
        $entryData[$fieldName] = '';
      }
    }
    
    // Handle special case for Apartment files_nr
    if( $type === 'Apartment' ) {
      $entryData['files_nr'] = $this->getNextFilesNr();
    }
    
    // Create directory structure
    $sanitizedName = sanitizeFilename($name);
    $typePrefix = $this->getTypePrefix($type);
    $entryName = "$typePrefix - $sanitizedName";
    
    $entryPath = empty($parentPath) ? "data/$entryName" : "data/$parentPath/$entryName";
    ensureDirectoryExists($entryPath);
    
    // Create data file
    $dataFile = "$entryPath/-this.md";
    $content = createFrontMatter($entryData, $description);
    
    if( !file_put_contents($dataFile, $content) ) {
      throw new Exception("Failed to create entry file");
    }
    
    return $id;
  }
  
  /**
   * Get type prefix for naming
   */
  private function getTypePrefix( $type )
  {
    // Simple implementation - could be made configurable
    static $prefixes = [
      'Activity' => '2',
      'Info' => '1',
      'Apartment' => '3'
    ];
    
    return $prefixes[$type] ?? '9';
  }
  
  /**
   * Get next files_nr for Apartment type
   */
  private function getNextFilesNr()
  {
    $jsonFile = 'types/Apartment/files_nr.json';
    
    if( file_exists($jsonFile) ) {
      $data = json_decode(file_get_contents($jsonFile), true);
      $lastId = $data['lastId'] ?? 0;
    } else {
      $lastId = 0;
    }
    
    $nextId = $lastId + 1;
    
    // Save back to file
    ensureDirectoryExists('types/Apartment');
    file_put_contents($jsonFile, json_encode(['lastId' => $nextId]));
    
    return str_pad($nextId, 4, '0', STR_PAD_LEFT);
  }
  
  /**
   * Save entry
   */
  public function saveEntry( $path, $fields )
  {
    $instance = $this->getInstance($path);
    if( !$instance ) {
      throw new Exception('Entry not found');
    }
    
    $type = $instance['type'];
    $typeFields = $this->typeManager->getTypeFields($type);
    
    // Validate fields
    foreach( $fields as $fieldName => $value ) {
      if( isset($typeFields[$fieldName]) ) {
        $this->typeManager->validateField($typeFields[$fieldName], $value);
      }
    }
    
    // Update instance data
    foreach( $fields as $fieldName => $value ) {
      $instance[$fieldName] = $value;
    }
    
    // Handle description separately
    $description = $fields['description'] ?? $instance['description'] ?? '';
    unset($instance['description']);
    
    // Save to file
    $dataFile = "data/$path/-this.md";
    $content = createFrontMatter($instance, $description);
    
    if( !file_put_contents($dataFile, $content) ) {
      throw new Exception("Failed to save entry");
    }
  }
  
  /**
   * Delete entry
   */
  public function deleteEntry( $path )
  {
    $entryPath = "data/$path";
    
    if( !file_exists($entryPath) ) {
      throw new Exception('Entry not found');
    }
    
    // Recursive delete
    $this->deleteDirectory($entryPath);
  }
  
  /**
   * Recursively delete directory
   */
  private function deleteDirectory( $dir )
  {
    if( !is_dir($dir) ) {
      return unlink($dir);
    }
    
    $items = scandir($dir);
    foreach( $items as $item ) {
      if( $item === '.' || $item === '..' ) {
        continue;
      }
      
      $itemPath = "$dir/$item";
      if( is_dir($itemPath) ) {
        $this->deleteDirectory($itemPath);
      } else {
        unlink($itemPath);
      }
    }
    
    return rmdir($dir);
  }
  
  /**
   * Render list of entries
   */
  public function renderList( $entries )
  {
    if( empty($entries) ) {
      return '<div class="list-group-item text-muted">No entries found</div>';
    }
    
    $html = '';
    
    foreach( $entries as $entry ) {
      $type = $entry['_type'];
      $path = $entry['_path'];
      
      // Load type-specific list renderer
      $rendererFile = "types/$type/list.php";
      
      if( file_exists($rendererFile) ) {
        ob_start();
        include $rendererFile;
        $cellContent = ob_get_clean();
      } else {
        // Default renderer
        $cellContent = $this->renderDefaultListCell($entry);
      }
      
      $html .= '<div class="list-group-item d-flex justify-content-between align-items-center" 
                     onclick="navigateToEntry(\'' . htmlspecialchars($path) . '\')">';
      $html .= '<div class="flex-grow-1">' . $cellContent . '</div>';
      $html .= '<div class="btn-group" role="group">';
      $html .= '<button class="btn btn-sm btn-outline-primary ms-2" 
                        onclick="editEntry(\'' . htmlspecialchars($path) . '\'); event.stopPropagation();" 
                        title="Edit">';
      $html .= 'Edit</button>';
      $html .= '<div class="dropdown">';
      $html .= '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                        type="button" data-bs-toggle="dropdown" onclick="event.stopPropagation();" 
                        title="More actions">';
      $html .= '⋮</button>';
      $html .= '<ul class="dropdown-menu">';
      $html .= '<li><a class="dropdown-item text-danger" href="#" 
                       onclick="deleteEntry(\'' . htmlspecialchars($path) . '\'); event.stopPropagation();">Delete</a></li>';
      $html .= '</ul></div></div></div>';
    }
    
    return $html;
  }
  
  /**
   * Default list cell renderer
   */
  private function renderDefaultListCell( $entry )
  {
    $name = htmlspecialchars($entry['name'] ?? 'Unnamed');
    $date = formatDateShort($entry['time'] ?? '');
    
    return "<div>$name</div><small class=\"text-muted\">$date</small>";
  }
  
  /**
   * Render resources list
   */
  public function renderResources( $resources )
  {
    if( empty($resources) ) {
      return '<div class="list-group-item text-muted">No resources found</div>';
    }
    
    $html = '';
    
    foreach( $resources as $resource ) {
      $name = htmlspecialchars($resource['name']);
      $icon = $resource['is_dir'] ? '📁' : '📄';
      $size = $resource['is_dir'] ? '' : ' (' . $this->formatFileSize($resource['size']) . ')';
      
      $html .= '<div class="list-group-item">';
      $html .= "<div>$icon $name$size</div>";
      $html .= '</div>';
    }
    
    return $html;
  }
  
  /**
   * Format file size
   */
  private function formatFileSize( $bytes )
  {
    if( $bytes >= 1048576 ) {
      return round($bytes / 1048576, 1) . ' MB';
    } elseif( $bytes >= 1024 ) {
      return round($bytes / 1024, 1) . ' KB';
    } else {
      return $bytes . ' B';
    }
  }
  
  /**
   * Render read-only view
   */
  public function renderReadOnly( $instance )
  {
    $type = $instance['type'];
    $rendererFile = "types/$type/read_only.php";
    
    if( file_exists($rendererFile) ) {
      ob_start();
      include $rendererFile;
      return ob_get_clean();
    }
    
    // Default read-only renderer
    $html = '<div class="card">';
    $html .= '<div class="card-body">';
    $html .= '<h5 class="card-title">' . htmlspecialchars($instance['name'] ?? 'Unnamed') . '</h5>';
    if( !empty($instance['description']) ) {
      $html .= '<p class="card-text">' . nl2br(htmlspecialchars($instance['description'])) . '</p>';
    }
    $html .= '<small class="text-muted">Created: ' . formatDateLong($instance['time'] ?? '') . '</small>';
    $html .= '</div></div>';
    
    return $html;
  }
  
  /**
   * Render edit form
   */
  public function renderEditForm( $instance )
  {
    $type = $instance['type'];
    $rendererFile = "types/$type/edit.php";
    
    if( file_exists($rendererFile) ) {
      ob_start();
      include $rendererFile;
      return ob_get_clean();
    }
    
    // Default edit form
    return $this->renderDefaultEditForm($instance);
  }
  
  /**
   * Default edit form renderer
   */
  private function renderDefaultEditForm( $instance )
  {
    $type = $instance['type'];
    $typeFields = $this->typeManager->getTypeFields($type);
    
    $html = '<form id="editForm">';
    
    // Default fields
    $html .= '<div class="mb-3">';
    $html .= '<label class="form-label">Name</label>';
    $html .= '<input type="text" class="form-control" name="name" value="' . 
             htmlspecialchars($instance['name'] ?? '') . '" required>';
    $html .= '</div>';
    
    $html .= '<div class="mb-3">';
    $html .= '<label class="form-label">Description</label>';
    $html .= '<textarea class="form-control" name="description" rows="3">' . 
             htmlspecialchars($instance['description'] ?? '') . '</textarea>';
    $html .= '</div>';
    
    // Type-specific fields
    foreach( $typeFields as $fieldName => $fieldDef ) {
      $html .= $this->renderFormField($fieldName, $fieldDef, $instance[$fieldName] ?? '');
    }
    
    $html .= '</form>';
    
    return $html;
  }
  
  /**
   * Render individual form field
   */
  private function renderFormField( $fieldName, $fieldDef, $value )
  {
    $label = ucfirst(str_replace('_', ' ', $fieldName));
    $required = ($fieldDef['required'] ?? false) ? 'required' : '';
    
    $html = '<div class="mb-3">';
    $html .= "<label class=\"form-label\">$label</label>";
    
    $type = $fieldDef['type'] ?? 'string';
    
    switch( $type ) {
      case 'int':
      case 'float':
        $inputType = 'number';
        $step = $type === 'float' ? 'step="0.01"' : '';
        $min = isset($fieldDef['min']) ? "min=\"{$fieldDef['min']}\"" : '';
        $max = isset($fieldDef['max']) ? "max=\"{$fieldDef['max']}\"" : '';
        
        $html .= "<input type=\"$inputType\" class=\"form-control\" name=\"$fieldName\" 
                         value=\"" . htmlspecialchars($value) . "\" $required $step $min $max>";
        break;
        
      case 'bool':
        $checked = in_array(strtolower($value), ['true', '1', 'yes']) ? 'checked' : '';
        $html .= "<div class=\"form-check\">";
        $html .= "<input type=\"checkbox\" class=\"form-check-input\" name=\"$fieldName\" 
                         value=\"true\" $checked $required>";
        $html .= "<label class=\"form-check-label\">$label</label>";
        $html .= "</div>";
        break;
        
      case 'hyperlink':
        $html .= "<input type=\"url\" class=\"form-control\" name=\"$fieldName\" 
                         value=\"" . htmlspecialchars($value) . "\" $required>";
        break;
        
      default:
        if( isset($fieldDef['values']) && is_array($fieldDef['values']) ) {
          // Dropdown
          $html .= "<select class=\"form-select\" name=\"$fieldName\" $required>";
          foreach( $fieldDef['values'] as $label => $optionValue ) {
            $selected = ($value == $optionValue) ? 'selected' : '';
            $html .= "<option value=\"" . htmlspecialchars($optionValue) . "\" $selected>" . 
                     htmlspecialchars($label) . "</option>";
          }
          $html .= "</select>";
        } else {
          // Text input
          $html .= "<input type=\"text\" class=\"form-control\" name=\"$fieldName\" 
                           value=\"" . htmlspecialchars($value) . "\" $required>";
        }
        break;
    }
    
    $html .= '</div>';
    
    return $html;
  }
  
  /**
   * Sort entries
   */
  public function sortEntries( $entries, $sortBy )
  {
    switch( $sortBy ) {
      case 'name':
        usort($entries, function($a, $b) {
          return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        });
        break;
        
      case 'time':
      default:
        usort($entries, function($a, $b) {
          $timeA = strtotime($a['time'] ?? '0');
          $timeB = strtotime($b['time'] ?? '0');
          return $timeB - $timeA; // Newest first
        });
        break;
    }
    
    return $entries;
  }
  
  /**
   * Save uploaded image
   */
  public function saveImage( $file, $path )
  {
    $uploadDir = "data/$path/images";
    ensureDirectoryExists($uploadDir);
    
    // Generate filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $extension;
    $filepath = "$uploadDir/$filename";
    
    if( !move_uploaded_file($file['tmp_name'], $filepath) ) {
      throw new Exception('Failed to save image');
    }
    
    return $filename;
  }
}
?>
