/**
 * Info specific JavaScript code
 */

// Function to handle info form submission
function submitInfoForm(callback) {
  const form = document.getElementById('info-edit-form');
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
  
  // Call AJAX to save
  ajaxCall('saveInfo', data, callback);
}

// Function to validate info form
function validateInfoForm() {
  const name = document.getElementById('info-name').value;
  
  if( !name ) {
    alert('Name is required');
    return false;
  }
  
  return true;
}
