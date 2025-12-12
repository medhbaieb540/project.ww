// admin-tournaments.js
// - Open/close Add Tournament modal
// - Open/close Edit Tournament modal
// - Prefill Edit modal from table row
// - JS validation: name required, future date, reward selected

document.addEventListener("DOMContentLoaded", function () {
  /* ===========================
     ADD TOURNAMENT MODAL
  ============================ */
  // match your HTML: #addModal, #openAddModal, #closeAddModal, #cancelAdd
  var addModal        = document.getElementById("addModal");
  var openAddBtn      = document.getElementById("openAddModal");
  var closeAddBtn     = document.getElementById("closeAddModal");
  var cancelAddBtn    = document.getElementById("cancelAdd");

  function openAdd() {
    if (addModal) addModal.style.display = "flex";
  }

  function closeAdd() {
    if (addModal) addModal.style.display = "none";
  }

  if (openAddBtn)   openAddBtn.addEventListener("click", openAdd);
  if (closeAddBtn)  closeAddBtn.addEventListener("click", closeAdd);
  if (cancelAddBtn) cancelAddBtn.addEventListener("click", closeAdd);



  /* ===========================
     EDIT TOURNAMENT MODAL
  ============================ */
  // match your HTML: #editModal, #closeEditModal, #cancelEdit
  var editModal        = document.getElementById("editModal");
  var closeEditBtn     = document.getElementById("closeEditModal");
  var cancelEditBtn    = document.getElementById("cancelEdit");

  function openEditModal(btn) {
    if (!editModal || !btn) return;

    var id          = btn.getAttribute("data-id") || "";
    var name        = btn.getAttribute("data-name") || "";
    var description = btn.getAttribute("data-description") || "";
    var start       = btn.getAttribute("data-start") || "";
    // uses data-reward-id from your HTML
    var rewardId    = btn.getAttribute("data-reward-id") || "";

    var idInput    = document.getElementById("edit_id");
    var nameInput  = document.getElementById("edit_name");
    var descInput  = document.getElementById("edit_description");
    var startInput = document.getElementById("edit_start_date");
    var rewardSel  = document.getElementById("edit_reward_id");

    if (!idInput || !nameInput || !startInput) {
      console.error("Edit modal inputs missing in DOM");
      return;
    }

    idInput.value       = id;
    nameInput.value     = name;
    if (descInput)  descInput.value  = description;
    if (startInput) startInput.value = start;      // already datetime-local format
    if (rewardSel)  rewardSel.value  = rewardId;

    editModal.style.display = "flex";
  }

  function closeEdit() {
    if (editModal) editModal.style.display = "none";
  }

  if (closeEditBtn)  closeEditBtn.addEventListener("click", closeEdit);
  if (cancelEditBtn) cancelEditBtn.addEventListener("click", closeEdit);

  // Event delegation for Edit buttons in table
  document.addEventListener("click", function (e) {
    var target = e.target;
    if (target.classList.contains("btn-edit")) {
      openEditModal(target);
    }
  });



  /* ===========================
     CLOSE MODALS BY CLICKING OUTSIDE
  ============================ */
  window.addEventListener("click", function (e) {
    if (e.target === addModal)  closeAdd();
    if (e.target === editModal) closeEdit();
  });



  /* ===========================
     JS VALIDATION – ADD FORM
  ============================ */
  var addForm = addModal ? addModal.querySelector("form") : null;

  if (addForm) {
    var addNameInput    = addForm.querySelector("input[name='name']");
    var addStartInput   = addForm.querySelector("input[name='start_date']");
    var addRewardSelect = addForm.querySelector("select[name='reward_id']");

    addForm.addEventListener("submit", function (e) {
      var errors = [];

      // Name required
      if (!addNameInput || addNameInput.value.trim() === "") {
        errors.push("Tournament name is required.");
      }

      // Start date required + future
      if (!addStartInput || addStartInput.value.trim() === "") {
        errors.push("Start date is required.");
      } else {
        var chosen = new Date(addStartInput.value);
        var now    = new Date();

        if (isNaN(chosen.getTime())) {
          errors.push("Invalid start date.");
        } else if (chosen <= now) {
          errors.push("Start date must be in the FUTURE.");
        }
      }

      // Reward required (if select exists)
      if (addRewardSelect) {
        if (!addRewardSelect.value || addRewardSelect.value === "") {
          errors.push("Please select a reward.");
        }
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
      }
    });
  }



  /* ===========================
     JS VALIDATION – EDIT FORM
  ============================ */
  var editForm = editModal ? editModal.querySelector("form") : null;

  if (editForm) {
    var editNameInput    = editForm.querySelector("input[name='name']");
    var editStartInput   = editForm.querySelector("input[name='start_date']");
    var editRewardSelect = editForm.querySelector("select[name='reward_id']");

    editForm.addEventListener("submit", function (e) {
      var errors = [];

      // Name required
      if (!editNameInput || editNameInput.value.trim() === "") {
        errors.push("Tournament name is required.");
      }

      // Start date required + future
      if (!editStartInput || editStartInput.value.trim() === "") {
        errors.push("Start date is required.");
      } else {
        var chosen = new Date(editStartInput.value);
        var now    = new Date();

        if (isNaN(chosen.getTime())) {
          errors.push("Invalid start date.");
        } else if (chosen <= now) {
          errors.push("Start date must be in the FUTURE.");
        }
      }

      // Reward required (if select exists)
      if (editRewardSelect) {
        if (!editRewardSelect.value || editRewardSelect.value === "") {
          errors.push("Please select a reward.");
        }
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
      }
    });
  }



  /* ===========================
     SEARCH + STATUS FILTER (ADMIN TABLE)
  ============================ */

  // from your HTML:
  // <select class="filter-select">, <input class="search-input">
  var searchInput  = document.querySelector(".search-input");
  var filterSelect = document.querySelector(".filter-select");
  var tbody        = document.getElementById("tournaments-body");
  var rows         = tbody ? Array.from(tbody.getElementsByTagName("tr")) : [];

  function applyAdminFilters() {
    var text   = searchInput ? searchInput.value.toLowerCase() : "";
    var status = filterSelect ? filterSelect.value : "all";

    rows.forEach(function (row) {
      // if it's the "No tournaments found" row with colspan, just show it
      var noDataCell = row.querySelector("td[colspan]");
      if (noDataCell) {
        row.style.display = "";
        return;
      }

      // first cell = title
      var titleCell = row.cells[0];
      var title     = titleCell ? titleCell.textContent.toLowerCase() : "";

      // status text from .status-pill (LIVE / UPCOMING / FINISHED)
      var statusSpan = row.querySelector(".status-pill");
      var rowStatus  = statusSpan
        ? statusSpan.textContent.trim().toLowerCase()   // "live", "upcoming", "finished"
        : "";

      var ok = true;

      // filter by search text
      if (text && !title.includes(text)) {
        ok = false;
      }

      // filter by status
      if (status !== "all" && rowStatus !== status) {
        ok = false;
      }

      row.style.display = ok ? "" : "none";
    });
  }

  if (searchInput) {
    searchInput.addEventListener("input", applyAdminFilters);
  }
  if (filterSelect) {
    filterSelect.addEventListener("change", applyAdminFilters);
  }
});
