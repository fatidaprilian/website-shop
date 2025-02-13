document.addEventListener('DOMContentLoaded', function() {
  // Smooth scroll untuk semua link anchor
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const targetId = this.getAttribute('href').substring(1);
          const target = document.getElementById(targetId);
          if (target) {
              target.scrollIntoView({
                  behavior: 'smooth',
                  block: 'start'
              });
          }
      });
  });
});
