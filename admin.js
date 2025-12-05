// ../../public/js/admin.js

// Get sidebar links and sections
const links    = document.querySelectorAll('.nav-link');
const sections = document.querySelectorAll('.section');

// Function to show a section by name (dashboard, users, games, …)
function showSection(sectionName) {
  // toggle sections
  sections.forEach(sec => {
    sec.classList.toggle('active', sec.id === 'section-' + sectionName);
  });

  // toggle active state on links
  links.forEach(link => {
    link.classList.toggle('active', link.dataset.section === sectionName);
  });
}

// Handle clicks on sidebar links
links.forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();

    const target = this.dataset.section; // "dashboard", "users", ...

    // Show right section
    showSection(target);

    // Update URL (?section=users)
    history.pushState({}, "", "?section=" + target);
  });
});

// On first load: read ?section=... from URL
const params = new URLSearchParams(window.location.search);
const defaultSection = params.get("section") || "dashboard";
showSection(defaultSection);

// Optional: handle back/forward browser buttons
window.addEventListener("popstate", () => {
  const params = new URLSearchParams(window.location.search);
  const sec = params.get("section") || "dashboard";
  showSection(sec);
});
