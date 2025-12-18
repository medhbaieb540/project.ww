// nav.js — set .active on header nav based on current file
(function() {
  function basename(path) {
    return path.split('/').pop();
  }

  var current = basename(window.location.pathname) || 'index.html';
  var navLinks = document.querySelectorAll('nav a');
  navLinks.forEach(function(a) {
    var href = a.getAttribute('href');
    if (!href) return;
    // normalize
    var h = href.split('/').pop();
    if (h === current || (current === '' && h === 'index.html')) {
      a.classList.add('active');
    }
  });
})();
