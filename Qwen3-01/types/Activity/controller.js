/**
 * Activity specific JavaScript code
 */

// Function to handle activity form submission
function submitActivityForm(callback) {
  const form = document.getElementById('activity-edit-form');
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
  ajaxCall('saveActivity', data, callback);
}

// Function to validate activity form
function validateActivityForm() {
  const name = document.getElementById('activity-name').value;
  const priority = document.getElementById('activity-priority').value;
  const state = document.getElementById('activity-state').value;
  
  if( !name ) {
    alert('Name is required');
    return false;
  }
  
  if( !priority ) {
    alert('Priority is required');
    return false;
  }
  
  if( !state ) {
    alert('State is required');
    return false;
  }
  
  return true;
}
