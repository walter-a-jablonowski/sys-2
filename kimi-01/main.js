// Global variables
let currentPath = '';
let currentEntries = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  currentPath = urlParams.get('path') || '';
  
  loadEntries();
  loadResources();
  
  // Event listeners
  document.getElementById('sortSelect').addEventListener('change', sortEntries);
});

// Load entries
async function loadEntries() {
  try {
    const response = await fetch('ajax.php?action=listEntries&path=' + encodeURIComponent(currentPath));
    const data = await response.json();
    
    if (data.error) {
      throw new Error(data.error);
    }
    
    currentEntries = data.entries;
    displayEntries();
  } catch (error) {
    showError('Failed to load entries: ' + error.message);
  }
}

// Display entries
function displayEntries() {
  const container = document.getElementById('entriesList');
  container.innerHTML = '';
  
  if (currentEntries.length === 0) {
    container.innerHTML = '<div class="col-12"><p class="text-muted text-center">No entries found</p></div>';
    return;
  }
  
  currentEntries.forEach(entry => {
    const card = createEntryCard(entry);
    container.appendChild(card);
  });
}

// Create entry card
function createEntryCard(entry) {
  const col = document.createElement('div');
  col.className = 'col-12 col-md-6 col-lg-4 mb-3';
  
  col.innerHTML = `
    <div class="card entry-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div class="list-cell-content flex-grow-1" data-id="${entry.id}" data-path="${entry.path}">
            ${entry.listContent}
          </div>
          <div class="btn-group ms-2">
            <button class="btn btn-sm btn-outline-primary" onclick="editEntry('${entry.id}', '${entry.path}')">
              <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item text-danger" href="#" onclick="deleteEntry('${entry.id}', '${entry.path}')">Delete</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Add click handler for navigation
  const content = col.querySelector('.list-cell-content');
  content.addEventListener('click', () => {
    if (entry.isDirectory) {
      window.location.href = '?path=' + encodeURIComponent(entry.path);
    }
  });
  
  return col;
}

// Sort entries
function sortEntries() {
  const sortBy = document.getElementById('sortSelect').value;
  
  currentEntries.sort((a, b) => {
    if (sortBy === 'time') {
      return new Date(b.time) - new Date(a.time);
    } else if (sortBy === 'name') {
      return a.name.localeCompare(b.name);
    }
  });
  
  displayEntries();
}

// Add entry
async function addEntry() {
  const form = document.getElementById('addForm');
  const formData = new FormData(form);
  
  const data = {
    type: formData.get('type'),
    name: formData.get('name'),
    description: formData.get('description'),
    path: currentPath
  };
  
  try {
    const response = await fetch('ajax.php?action=addEntry', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.error) {
      throw new Error(result.error);
    }
    
    // Close modal and reload
    const modal = bootstrap.Modal.getInstance(document.getElementById('addModal'));
    modal.hide();
    form.reset();
    loadEntries();
    
  } catch (error) {
    showError('Failed to add entry: ' + error.message);
  }
}

// Edit entry
async function editEntry(id, path) {
  try {
    const response = await fetch(`ajax.php?action=getEditForm&id=${encodeURIComponent(id)}&path=${encodeURIComponent(path)}`);
    const data = await response.json();
    
    if (data.error) {
      throw new Error(data.error);
    }
    
    document.getElementById('editModalBody').innerHTML = data.form;
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
    
  } catch (error) {
    showError('Failed to load edit form: ' + error.message);
  }
}

// Save entry
async function saveEntry() {
  const form = document.getElementById('editForm');
  if (!form) return;
  
  const formData = new FormData(form);
  const data = Object.fromEntries(formData);
  
  try {
    const response = await fetch('ajax.php?action=saveEntry', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.error) {
      throw new Error(result.error);
    }
    
    // Close modal and reload
    const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
    modal.hide();
    loadEntries();
    
  } catch (error) {
    showError('Failed to save entry: ' + error.message);
  }
}

// Delete entry
async function deleteEntry(id, path) {
  if (!confirm('Are you sure you want to delete this entry?')) {
    return;
  }
  
  try {
    const response = await fetch('ajax.php?action=deleteEntry', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id, path })
    });
    
    const result = await response.json();
    
    if (result.error) {
      throw new Error(result.error);
    }
    
    loadEntries();
    
  } catch (error) {
    showError('Failed to delete entry: ' + error.message);
  }
}

// Load resources
async function loadResources() {
  try {
    const response = await fetch('ajax.php?action=listResources&path=' + encodeURIComponent(currentPath));
    const data = await response.json();
    
    if (data.error) {
      throw new Error(data.error);
    }
    
    displayResources(data.resources);
  } catch (error) {
    showError('Failed to load resources: ' + error.message);
  }
}

// Display resources
function displayResources(resources) {
  const container = document.getElementById('resourcesList');
  container.innerHTML = '';
  
  if (resources.length === 0) {
    container.innerHTML = '<p class="text-muted">No resources found</p>';
    return;
  }
  
  const list = document.createElement('div');
  list.className = 'list-group';
  
  resources.forEach(resource => {
    const item = document.createElement('a');
    item.className = 'list-group-item list-group-item-action';
    item.href = resource.url;
    item.target = '_blank';
    item.innerHTML = `
      <i class="bi ${resource.isDirectory ? 'bi-folder' : 'bi-file-earmark'}"></i>
      ${resource.name}
    `;
    list.appendChild(item);
  });
  
  container.appendChild(list);
}

// Show error
function showError(message) {
  const alert = document.createElement('div');
  alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
  alert.style.top = '70px';
  alert.style.right = '10px';
  alert.style.zIndex = '9999';
  alert.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(alert);
  
  setTimeout(() => {
    if (alert.parentNode) {
      alert.remove();
    }
  }, 5000);
}

// Camera upload for Apartment
globalThis.uploadCameraImage = async function(entryId, entryPath) {
  try {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      throw new Error('Camera not supported');
    }
    
    // Create file input for camera
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.capture = 'environment';
    
    input.onchange = async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      
      const formData = new FormData();
      formData.append('image', file);
      formData.append('entryId', entryId);
      formData.append('entryPath', entryPath);
      
      const response = await fetch('ajax.php?action=uploadImage', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if (result.error) {
        throw new Error(result.error);
      }
      
      // Reload the edit form to show new image
      editEntry(entryId, entryPath);
    };
    
    input.click();
    
  } catch (error) {
    showError('Camera upload failed: ' + error.message);
  }
};
