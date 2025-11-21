const form = document.getElementById("loginForm");
const status = document.getElementById("status");

form.addEventListener("submit", (e) => {
     e.preventDefault();
     const username = document.getElementById("username").value;
     const password = document.getElementById("password").value;
        if (!username || !password) {
            status.textContent = "Please fill in both fields.";
            status.style.color = "red";
        }else if (username === "admin" && password === "admin") {
            status.textContent = "Login successful!";
            status.style.color = "green";
        }
         else {
            status.textContent = "Invalid username or password.";
            status.style.color = "red";
        }
        
}); 