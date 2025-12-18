// assets/js/admin-rewards.js
// Simple JS "controle de saisie" for Add Reward form

document.addEventListener("DOMContentLoaded", function () {
  // Find the Add Reward form
  var addForm = document.querySelector(".add-form");
  if (!addForm) return;

  var titleInput = addForm.querySelector("input[name='title']");
  var valueInput = addForm.querySelector("input[name='value']");
  var typeSelect = addForm.querySelector("select[name='type']");

  addForm.addEventListener("submit", function (e) {
    var errors = [];

    // ----- Title: required, min length -----
    if (!titleInput || titleInput.value.trim() === "") {
      errors.push("Title is required.");
    } else if (titleInput.value.trim().length < 3) {
      errors.push("Title must be at least 3 characters long.");
    }

    // ----- Value: required, numeric, > 0 -----
    if (!valueInput || valueInput.value.trim() === "") {
      errors.push("Value is required.");
    } else {
      var num = Number(valueInput.value);
      if (isNaN(num)) {
        errors.push("Value must be a number.");
      } else if (num <= 0) {
        errors.push("Value must be greater than 0.");
      }
    }

    // ----- Type: must be chosen -----
    if (!typeSelect || typeSelect.value === "") {
      errors.push("Please select a reward type.");
    }

    // If we collected any errors → block submit + show them
    if (errors.length > 0) {
      e.preventDefault();
      alert(errors.join("\n"));
    }
  });
});
