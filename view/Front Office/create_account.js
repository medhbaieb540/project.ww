// Get form and input elements
const form = document.getElementById("signupForm");
const usernameInput = document.getElementById("signupUsername");
const emailInput = document.getElementById("signupEmail");
const passwordInput = document.getElementById("signupPassword");
const confirmInput = document.getElementById("signupConfirm");
const accountTypeSelect = document.getElementById("accountType");
const termsCheckbox = document.getElementById("terms");

// Get status elements
const usernameStatus = document.getElementById("UsernameStatus");
const emailStatus = document.getElementById("EmailStatus");
const passwordStatus = document.getElementById("PasswordStatus");
const confirmStatus = document.getElementById("ConfirmStatus");
const formStatus = document.getElementById("signupStatus");

// Real-time validation for Username
usernameInput.addEventListener("keyup", () => {
  const username = usernameInput.value.trim();
  if (!username) {
    usernameStatus.textContent = "Please enter a username.";
    usernameStatus.className = "status error";
  } else if (username.length < 3) {
    usernameStatus.textContent = "Username must be at least 3 characters.";
    usernameStatus.className = "status error";
  } else {
    usernameStatus.textContent = "✓ Valid username.";
    usernameStatus.className = "status success";
  }
});

// Real-time validation for Email
emailInput.addEventListener("keyup", () => {
  const email = emailInput.value.trim();
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  if (!email) {
    emailStatus.textContent = "Please enter an email address.";
    emailStatus.className = "status error";
  } else if (!emailPattern.test(email)) {
    emailStatus.textContent = "Please enter a valid email address.";
    emailStatus.className = "status error";
  } else {
    emailStatus.textContent = "✓ Valid email.";
    emailStatus.className = "status success";
  }
});

// Real-time validation for Password
passwordInput.addEventListener("keyup", () => {
  const password = passwordInput.value;
  
  if (!password) {
    passwordStatus.textContent = "Please enter a password.";
    passwordStatus.className = "status error";
  } else if (password.length < 8) {
    passwordStatus.textContent = "Password must be at least 8 characters.";
    passwordStatus.className = "status error";
  } else {
    passwordStatus.textContent = "✓ Strong password.";
    passwordStatus.className = "status success";
  }
  
  // Also validate confirm password if it has content
  if (confirmInput.value) {
    confirmInput.dispatchEvent(new Event("keyup"));
  }
});

// Real-time validation for Confirm Password
confirmInput.addEventListener("keyup", () => {
  const password = passwordInput.value;
  const confirmPassword = confirmInput.value;
  
  if (!confirmPassword) {
    confirmStatus.textContent = "Please confirm your password.";
    confirmStatus.className = "status error";
  } else if (confirmPassword !== password) {
    confirmStatus.textContent = "Passwords do not match.";
    confirmStatus.className = "status error";
  } else {
    confirmStatus.textContent = "✓ Passwords match.";
    confirmStatus.className = "status success";
  }
});

// Real-time validation for Account Type
accountTypeSelect.addEventListener("change", () => {
  const accountType = accountTypeSelect.value;
  
  if (!accountType) {
    formStatus.textContent = "Please select an account type.";
    formStatus.className = "status error";
  } else {
    formStatus.textContent = `✓ Account type: ${accountType.charAt(0).toUpperCase() + accountType.slice(1)} selected.`;
    formStatus.className = "status success";
  }
});



// Form submission validation
form.addEventListener("submit", function (e) {
  const username = usernameInput.value.trim();
  const email = emailInput.value.trim();
  const password = passwordInput.value;
  const confirmPassword = confirmInput.value;
  const role = accountTypeSelect.value;
  const termsAccepted = termsCheckbox.checked;
  
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  // Validate all fields; prevent submission only on validation failure
  if (!username || !email || !password || !confirmPassword || !role) {
    e.preventDefault();
    formStatus.textContent = "Please fill in all fields.";
    formStatus.className = "status error";
    return;
  }
  
  if (username.length < 3) {
    e.preventDefault();
    formStatus.textContent = "Username must be at least 3 characters.";
    formStatus.className = "status error";
    return;
  }
  
  if (!emailPattern.test(email)) {
    e.preventDefault();
    formStatus.textContent = "Please enter a valid email address.";
    formStatus.className = "status error";
    return;
  }
  
  if (password.length < 8) {
    e.preventDefault();
    formStatus.textContent = "Password must be at least 8 characters.";
    formStatus.className = "status error";
    return;
  }
  
  if (password !== confirmPassword) {
    e.preventDefault();
    formStatus.textContent = "Passwords do not match.";
    formStatus.className = "status error";
    return;
  }
  
  if (!termsAccepted) {
    e.preventDefault();
    formStatus.textContent = "You must accept the terms and conditions.";
    formStatus.className = "status error";
    return;
  }
  
  // All validations passed — allow normal form submission to backend
  formStatus.textContent = "Creating account...";
  formStatus.className = "status";
  // form will submit naturally (action + method)
});