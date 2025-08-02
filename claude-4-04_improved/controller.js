// Global JavaScript utilities and event handlers

// Global AJAX utility function
async function ajaxCall(functionName, data = {}, type = null) {
  try {
    const formData = new FormData();
    formData.append('function', functionName);
    
    if (type) {
      formData.append('type', type);
    }
    
    // Add all data fields
    for (const [key, value] of Object.entries(data)) {
      if (value instanceof File) {
        formData.append(key, value);
      } else {
        formData.append(key, value);
      }
    }
    
    const response = await fetch('ajax.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (!result.success) {
      throw new Error(result.error || 'Unknown error occurred');
    }
    
    return result;
  } catch (error) {
    console.error('AJAX Error:', error);
    showError(error.message);
    throw error;
  }
}

// Error display function
function showError(message) {
  // Create or update error alert
  let errorAlert = document.getElementById('errorAlert');
  if (!errorAlert) {
    errorAlert = document.createElement('div');
    errorAlert.id = 'errorAlert';
    errorAlert.className = 'alert alert-danger alert-dismissible fade show';
    errorAlert.innerHTML = `
      <span id="errorMessage"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.content-area').insertBefore(errorAlert, document.querySelector('.content-area').firstChild);
  }
  
  document.getElementById('errorMessage').textContent = message;
  errorAlert.style.display = 'block';
}

// Success display function
function showSuccess(message) {
  const successAlert = document.createElement('div');
  successAlert.className = 'alert alert-success alert-dismissible fade show';
  successAlert.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.querySelector('.content-area').insertBefore(successAlert, document.querySelector('.content-area').firstChild);
  
  // Auto-dismiss after 3 seconds
  setTimeout(() => {
    if (successAlert.parentNode) {
      successAlert.remove();
    }
  }, 3000);
}

// Initialize page
function initializePage() {
  // Load available types for add modal
  loadAvailableTypes();
  
  // Set up entry card click handlers
  document.querySelectorAll('.entry-card').forEach(card => {
    card.addEventListener('click', function(e) {
      // Don't navigate if clicking on dropdown or buttons
      if (e.target.closest('.dropdown') || e.target.closest('button')) {
        return;
      }
      
      const path = this.dataset.path;
      navigateToPath(path);
    });
  });
  
  // Set up sort handler
  document.getElementById('sortBy').addEventListener('change', function() {
    sortEntries(this.value);
  });
}

// Navigation
function navigateToPath(path) {
  const url = new URL(window.location);
  url.searchParams.set('path', path);
  window.location.href = url.toString();
}

// Load available types for current path
async function loadAvailableTypes() {
  try {
    const result = await ajaxCall('getAvailableTypes', { path: window.currentPath });
    const typeButtons = document.getElementById('typeButtons');
    typeButtons.innerHTML = '';
    
    result.types.forEach(type => {
      const button = document.createElement('button');
      button.className = 'btn btn-outline-primary me-2 mb-2';
      button.textContent = type.name;
      button.onclick = () => selectType(type.id);
      typeButtons.appendChild(button);
    });
  } catch (error) {
    console.error('Failed to load types:', error);
  }
}

// Show add modal
function showAddModal() {
  const modal = new bootstrap.Modal(document.getElementById('addModal'));
  document.getElementById('typeSelection').style.display = 'block';
  document.getElementById('entryForm').style.display = 'none';
  modal.show();
}

// Select type in add modal
async function selectType(typeId) {
  try {
    const result = await ajaxCall('loadAddForm', { type: typeId, path: window.currentPath });
    
    document.getElementById('typeSelection').style.display = 'none';
    document.getElementById('entryForm').style.display = 'block';
    document.getElementById('entryForm').innerHTML = result.html;
    
    // Initialize type-specific JavaScript if available
    if (window[`init${typeId}Form`]) {
      window[`init${typeId}Form`]();
    }
  } catch (error) {
    console.error('Failed to load form:', error);
  }
}

// Submit add form
async function submitAddForm(typeId) {
  try {
    const form = document.getElementById('addEntryForm');
    const formData = new FormData(form);
    
    const data = {};
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }
    data.path = window.currentPath;
    
    const result = await ajaxCall('saveEntry', data, typeId);
    
    showSuccess('Entry created successfully');
    bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
    
    // Reload page to show new entry
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  } catch (error) {
    console.error('Failed to save entry:', error);
  }
}

// Edit entry
async function editEntry(path) {
  try {
    const result = await ajaxCall('loadEditForm', { path: path });
    
    document.getElementById('editForm').innerHTML = result.html;
    
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
    
    // Initialize type-specific JavaScript if available
    if (result.type && window[`init${result.type}Form`]) {
      window[`init${result.type}Form`]();
    }
  } catch (error) {
    console.error('Failed to load edit form:', error);
  }
}

// Submit edit form
async function submitEditForm(typeId, path) {
  try {
    const form = document.getElementById('editEntryForm');
    const formData = new FormData(form);
    
    const data = {};
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }
    data.path = path;
    
    const result = await ajaxCall('saveEntry', data, typeId);
    
    showSuccess('Entry updated successfully');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    
    // Reload page to show changes
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  } catch (error) {
    console.error('Failed to update entry:', error);
  }
}

// Delete entry
function deleteEntry(path) {
  const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
  
  document.getElementById('confirmDelete').onclick = async function() {
    try {
      await ajaxCall('deleteEntry', { path: path });
      
      showSuccess('Entry deleted successfully');
      modal.hide();
      
      // Reload page to reflect deletion
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } catch (error) {
      console.error('Failed to delete entry:', error);
    }
  };
  
  modal.show();
}

// Sort entries
function sortEntries(sortBy) {
  const entryList = document.getElementById('entryList');
  const entries = Array.from(entryList.children);
  
  entries.sort((a, b) => {
    if (sortBy === 'name') {
      const nameA = a.querySelector('.card-body').textContent.trim().toLowerCase();
      const nameB = b.querySelector('.card-body').textContent.trim().toLowerCase();
      return nameA.localeCompare(nameB);
    } else {
      // Sort by time (default)
      const timeA = a.dataset.time || '0';
      const timeB = b.dataset.time || '0';
      return timeB.localeCompare(timeA); // Newest first
    }
  });
  
  // Re-append sorted entries
  entries.forEach(entry => entryList.appendChild(entry));
}

// Camera functionality for apartment type
async function captureImage(inputId) {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: 'environment' } // Use back camera on mobile
    });
    
    // Create video element for preview
    const video = document.createElement('video');
    video.srcObject = stream;
    video.play();
    
    // Create canvas for capture
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    
    // Show camera modal (you would implement this)
    // For now, just capture after 3 seconds
    setTimeout(() => {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0);
      
      // Convert to blob and set to file input
      canvas.toBlob(blob => {
        const file = new File([blob], 'camera-image.jpg', { type: 'image/jpeg' });
        const input = document.getElementById(inputId);
        
        // Create a new FileList with the captured image
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
        
        // Show preview
        showImagePreview(input);
      }, 'image/jpeg', 0.8);
      
      // Stop camera
      stream.getTracks().forEach(track => track.stop());
    }, 3000);
    
  } catch (error) {
    console.error('Camera access failed:', error);
    showError('Camera access failed. Please use file upload instead.');
  }
}

// Show image preview
function showImagePreview(input) {
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      let preview = input.parentNode.querySelector('.image-preview');
      if (!preview) {
        preview = document.createElement('img');
        preview.className = 'image-preview img-thumbnail mt-2';
        preview.style.maxWidth = '200px';
        input.parentNode.appendChild(preview);
      }
      preview.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}
