document.addEventListener('DOMContentLoaded', function() {
    const menuIcon = document.getElementById('menuIcon');
    const dropdownMenu = document.getElementById('dropdownMenu');
  
    menuIcon.addEventListener('click', function(event) {
      dropdownMenu.classList.toggle('show');
      event.stopPropagation();
    });
  
    // Hide dropdown when clicking outside
    document.addEventListener('click', function() {
      dropdownMenu.classList.remove('show');
    });
  
    // Prevent menu from closing when clicking inside
    dropdownMenu.addEventListener('click', function(event) {
      event.stopPropagation();
    });
  });