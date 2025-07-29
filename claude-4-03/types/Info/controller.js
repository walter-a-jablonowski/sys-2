// Info-specific JavaScript functionality

class InfoController
{
  static init()
  {
    // Info-specific initialization if needed
    console.log('Info controller initialized');
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  InfoController.init();
});
