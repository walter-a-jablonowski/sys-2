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

// Utility to generate ID
function generateId(name) {
  // Convert each word to first character uppercase
  let idName = name.replace(/\b\w/g, function(char) {
    return char.toUpperCase();
  });
  
  // Remove all non-alphanumeric characters
  idName = idName.replace(/[^a-zA-Z0-9]/g, '');
  
  // Add unique user and date
  const now = new Date();
  const year = String(now.getFullYear()).slice(-2);
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  
  return `${idName}-Default-${year}${month}${day}${hours}${minutes}${seconds}`;
}
