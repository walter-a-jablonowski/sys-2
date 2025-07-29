// Apartment-specific JavaScript functionality

class ApartmentController
{
  static init()
  {
    // Bind image upload functionality
    document.addEventListener('click', (e) => {
      if( e.target.id === 'uploadImages' )
      {
        ApartmentController.handleImageUpload();
      }
    });
  }

  static async handleImageUpload()
  {
    const fileInput = document.getElementById('apartmentImageUpload');
    const apartmentPath = document.getElementById('editEntryPath').value;
    
    if( ! fileInput.files.length )
    {
      entryManager.showError('Please select at least one image');
      return;
    }
    
    for( let i = 0; i < fileInput.files.length; i++ )
    {
      const file = fileInput.files[i];
      await ApartmentController.uploadSingleImage(file, apartmentPath);
    }
    
    // Clear the file input
    fileInput.value = '';
    entryManager.showSuccess('Images uploaded successfully');
  }

  static async uploadSingleImage( file, apartmentPath )
  {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('apartmentPath', apartmentPath);
    
    try {
      // Use the proper AJAX forwarding system
      const uploadResponse = await fetch('types/Apartment/ajax/uploadImage.php', {
        method: 'POST',
        body: formData
      });
      
      const result = await uploadResponse.json();
      
      if( ! result.success )
        throw new Error(result.error);
        
      return result;
    }
    catch( error ) {
      entryManager.showError('Failed to upload image: ' + error.message);
      throw error;
    }
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  ApartmentController.init();
});
