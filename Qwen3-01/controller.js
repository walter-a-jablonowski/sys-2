/**
 * Commonly used JavaScript code
 */

// Global error handling
window.addEventListener('error', function(e) {
  alert('Error: ' + e.message);
});

// AJAX utility function
function ajaxCall(action, data, callback) {
  fetch('ajax.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      action: action,
      data: data
    })
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

// Utility to format date as YYYY-MM-DD HH:MM:SS
function formatDateTime(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// Utility to generate ID as YYMMDDHHMMSS
function generateId(name) {
  const date = new Date();
  const year = String(date.getFullYear()).slice(-2);
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  
  // Create a simple hash of the name
  let hash = 0;
  for( let i = 0; i < name.length; i++ ) {
    hash = ((hash << 5) - hash) + name.charCodeAt(i);
    hash = hash & hash; // Convert to 32-bit integer
  }
  
  // Use last 2 digits of hash as additional identifier
  const hashSuffix = Math.abs(hash) % 100;
  
  return `${year}${month}${day}${hours}${minutes}${seconds}${String(hashSuffix).padStart(2, '0')}`;
}
