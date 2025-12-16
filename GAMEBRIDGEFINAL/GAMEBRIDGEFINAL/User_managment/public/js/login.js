// ../FrontOffice/login.js

document.addEventListener("DOMContentLoaded", () => {
  const form   = document.getElementById("loginForm");
  const emailInput = document.querySelector("input[name='email']");
  const passInput  = document.querySelector("input[name='password']");
  const statusDiv  = document.getElementById("status");


  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener("submit", (e) => {
   
    statusDiv.textContent = "";
    statusDiv.classList.remove("error", "success");

    const email = emailInput.value.trim();
    const pass  = passInput.value;

 
    if (!email || !pass) {
      e.preventDefault();
      statusDiv.textContent = "Please fill in both email and password.";
      statusDiv.classList.add("error");
      return;
    }

   
    if (!emailPattern.test(email)) {
      e.preventDefault();
      statusDiv.textContent = "Please enter a valid email address.";
      statusDiv.classList.add("error");
      return;
    }

    if (pass.length < 8) {
      e.preventDefault();
      statusDiv.textContent = "Password must be at least 6 characters.";
      statusDiv.classList.add("error");
      return;
    }

    // 
    statusDiv.textContent = "Checking your account...";
    statusDiv.classList.add("success");
    
  });
});
