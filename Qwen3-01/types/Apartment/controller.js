/**
 * Apartment specific JavaScript code
 */

// Function to handle apartment form submission
function submitApartmentForm(callback) {
  const form = document.getElementById('apartment-edit-form');
  const formData = new FormData(form);
  const data = {};
  
  // Convert FormData to object
  for( let [key, value] of formData.entries() ) {
    data[key] = value;
  }
  
  // Add time field
  data.time = formatDateTime(new Date());
  
  // Generate ID based on name
  if( data.name ) {
    data.id = generateId(data.name);
  }
  
  // Get next file number if this is a new apartment
  if( !data.files_nr ) {
    ajaxCall('getNextFileNumber', {}, function(error, result) {
      if( error ) {
        callback(error);
        return;
      }
      
      data.files_nr = result.fileNumber;
      
      // Call AJAX to save
      ajaxCall('saveApartment', data, callback);
    });
  } else {
    // Call AJAX to save
    ajaxCall('saveApartment', data, callback);
  }
}

// Function to validate apartment form
function validateApartmentForm() {
  const name = document.getElementById('apartment-name').value;
  const state = document.getElementById('apartment-state').value;
  
  if( !name ) {
    alert('Name is required');
    return false;
  }
  
  if( !state ) {
    alert('Status is required');
    return false;
  }
  
  return true;
}

// Function to handle image capture
function captureApartmentImage(apartmentId, callback) {
  // In a real implementation, this would use the device camera
  // For now, we'll simulate this with a file input
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.capture = 'environment'; // Use rear camera if available
  
  input.onchange = function(event) {
    const file = event.target.files[0];
    if( file ) {
      // Send image to server
      const formData = new FormData();
      formData.append('image', file);
      formData.append('apartmentId', apartmentId);
      
      fetch('ajax.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(result => {
        if( result.success ) {
          callback(null, result.data);
        } else {
          callback(result.error || 'Unknown error');
        }
      })
      .catch(error => {
        callback(error.message);
      });
    }
  };
  
  input.click();
}
