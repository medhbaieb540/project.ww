// ONLY links that switch sections
const links = document.querySelectorAll('.nav-link[data-section]');
const sections = document.querySelectorAll('.section');

function showSection(sectionName) {
  sections.forEach(sec => {
    sec.classList.toggle('active', sec.id === 'section-' + sectionName);
  });

  links.forEach(link => {
    link.classList.toggle('active', link.dataset.section === sectionName);
  });
}

links.forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    const target = this.dataset.section;
    showSection(target);
    history.pushState({}, "", "?section=" + target);
  });
});

const params = new URLSearchParams(window.location.search);
const defaultSection = params.get("section") || "dashboard";
showSection(defaultSection);

window.addEventListener("popstate", () => {
  const params = new URLSearchParams(window.location.search);
  const sec = params.get("section") || "dashboard";
  showSection(sec);
});
