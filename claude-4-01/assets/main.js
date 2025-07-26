// Main JavaScript for the hierarchical data management system

let currentPath = '';

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  // Get current path from URL
  const urlParams = new URLSearchParams(window.location.search);
  currentPath = urlParams.get('path') || '';
  
  // Initialize tooltips and other Bootstrap components
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

/**
 * Navigate back to parent level
 */
function navigateBack()
{
  if( currentPath === '' ) {
    return;
  }
  
  const pathParts = currentPath.split('/');
  pathParts.pop();
  const parentPath = pathParts.join('/');
  
  window.location.href = 'index.php' + (parentPath ? '?path=' + encodeURIComponent(parentPath) : '');
}

/**
 * Navigate to entry (double click)
 */
function navigateToEntry( entryPath )
{
  window.location.href = 'index.php?path=' + encodeURIComponent(entryPath);
}

/**
 * Show add entry modal
 */
function showAddModal()
{
  const modal = new bootstrap.Modal(document.getElementById('addModal'));
  modal.show();
}

/**
 * Add new entry
 */
async function addEntry()
{
  try {
    const form = document.getElementById('addForm');
    const formData = new FormData(form);
    
    const data = {
      type: formData.get('entryType'),
      name: formData.get('entryName'),
      description: formData.get('entryDescription'),
      path: currentPath
    };
    
    // Validate required fields
    if( !data.type || !data.name ) {
      showError('Please fill in all required fields');
      return;
    }
    
    const response = await fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'add_entry',
        data: data
      })
    });
    
    const result = await response.json();
    
    if( result.success ) {
      // Close modal and refresh page
      bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
      location.reload();
    } else {
      showError(result.error || 'Failed to add entry');
    }
    
  } catch( error ) {
    showError('Network error: ' + error.message);
  }
}

/**
 * Edit entry (single click)
 */
async function editEntry( entryPath )
{
  try {
    const response = await fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'edit_entry',
        data: { path: entryPath }
      })
    });
    
    const result = await response.json();
    
    if( result.success ) {
      document.getElementById('editModalBody').innerHTML = result.data.html;
      
      // Store current edit path
      document.getElementById('editModal').setAttribute('data-path', entryPath);
      
      const modal = new bootstrap.Modal(document.getElementById('editModal'));
      modal.show();
    } else {
      showError(result.error || 'Failed to load entry');
    }
    
  } catch( error ) {
    showError('Network error: ' + error.message);
  }
}

/**
 * Save edited entry
 */
async function saveEntry()
{
  try {
    const modal = document.getElementById('editModal');
    const entryPath = modal.getAttribute('data-path');
    const form = document.getElementById('editForm');
    
    if( !form ) {
      showError('Form not found');
      return;
    }
    
    const formData = new FormData(form);
    const fields = {};
    
    for( const [key, value] of formData.entries() ) {
      fields[key] = value;
    }
    
    const response = await fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'save_entry',
        data: {
          path: entryPath,
          fields: fields
        }
      })
    });
    
    const result = await response.json();
    
    if( result.success ) {
      // Close modal and refresh page
      bootstrap.Modal.getInstance(modal).hide();
      location.reload();
    } else {
      showError(result.error || 'Failed to save entry');
    }
    
  } catch( error ) {
    showError('Network error: ' + error.message);
  }
}

/**
 * Delete entry
 */
async function deleteEntry( entryPath )
{
  if( !confirm('Are you sure you want to delete this entry? This action cannot be undone.') ) {
    return;
  }
  
  try {
    const response = await fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'delete_entry',
        data: { path: entryPath }
      })
    });
    
    const result = await response.json();
    
    if( result.success ) {
      location.reload();
    } else {
      showError(result.error || 'Failed to delete entry');
    }
    
  } catch( error ) {
    showError('Network error: ' + error.message);
  }
}

/**
 * Edit current instance
 */
function editCurrentInstance()
{
  if( currentPath ) {
    editEntry(currentPath);
  }
}

/**
 * Delete current instance
 */
function deleteCurrentInstance()
{
  if( currentPath ) {
    if( confirm('Are you sure you want to delete this entry and navigate back? This action cannot be undone.') ) {
      deleteEntry(currentPath).then(() => {
        navigateBack();
      });
    }
  }
}

/**
 * Sort list
 */
async function sortList()
{
  try {
    const sortBy = document.getElementById('sortSelect').value;
    
    const response = await fetch('ajax.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'get_list',
        data: {
          path: currentPath,
          sort: sortBy
        }
      })
    });
    
    const result = await response.json();
    
    if( result.success ) {
      document.getElementById('entryList').innerHTML = result.data.html;
    } else {
      showError(result.error || 'Failed to sort list');
    }
    
  } catch( error ) {
    showError('Network error: ' + error.message);
  }
}

/**
 * Handle camera image upload (for Apartment type)
 */
async function uploadImage( entryPath )
{
  try {
    // Check if device supports camera
    if( !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia ) {
      showError('Camera not supported on this device');
      return;
    }
    
    // Create file input for camera
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.capture = 'environment'; // Use rear camera
    
    input.onchange = async function(event) {
      const file = event.target.files[0];
      if( !file ) return;
      
      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
      if( !allowedTypes.includes(file.type) ) {
        showError('Invalid file type. Only image files are allowed.');
        return;
      }
      
      // Upload file
      const formData = new FormData();
      formData.append('image', file);
      formData.append('path', entryPath);
      
      const response = await fetch('ajax.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      if( result.success ) {
        showSuccess('Image uploaded successfully: ' + result.data.filename);
        // Optionally refresh resources tab
        location.reload();
      } else {
        showError(result.error || 'Failed to upload image');
      }
    };
    
    input.click();
    
  } catch( error ) {
    showError('Camera error: ' + error.message);
  }
}

/**
 * Show error message
 */
function showError( message )
{
  console.error('Error:', message);
  
  const errorToast = document.getElementById('errorToast');
  const errorMessage = document.getElementById('errorMessage');
  
  errorMessage.textContent = message;
  
  const toast = new bootstrap.Toast(errorToast);
  toast.show();
}

/**
 * Show success message
 */
function showSuccess( message )
{
  console.log('Success:', message);
  
  // Create success toast if it doesn't exist
  let successToast = document.getElementById('successToast');
  if( !successToast ) {
    const container = document.querySelector('.toast-container');
    successToast = document.createElement('div');
    successToast.id = 'successToast';
    successToast.className = 'toast';
    successToast.setAttribute('role', 'alert');
    successToast.innerHTML = `
      <div class="toast-header bg-success text-white">
        <strong class="me-auto">Success</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body" id="successMessage"></div>
    `;
    container.appendChild(successToast);
  }
  
  document.getElementById('successMessage').textContent = message;
  
  const toast = new bootstrap.Toast(successToast);
  toast.show();
}

/**
 * Handle form validation errors
 */
function handleValidationError( field, message )
{
  const fieldElement = document.querySelector(`[name="${field}"]`);
  if( fieldElement ) {
    fieldElement.classList.add('is-invalid');
    
    // Add error message
    let feedback = fieldElement.parentNode.querySelector('.invalid-feedback');
    if( !feedback ) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      fieldElement.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
    
    // Remove error on input
    fieldElement.addEventListener('input', function() {
      fieldElement.classList.remove('is-invalid');
      if( feedback ) {
        feedback.remove();
      }
    }, { once: true });
  }
}

/**
 * Clear form validation
 */
function clearValidation( form )
{
  const invalidFields = form.querySelectorAll('.is-invalid');
  invalidFields.forEach(field => {
    field.classList.remove('is-invalid');
  });
  
  const feedbacks = form.querySelectorAll('.invalid-feedback');
  feedbacks.forEach(feedback => {
    feedback.remove();
  });
}
