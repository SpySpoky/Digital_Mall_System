'use strict'

const form = document.querySelector('#loginForm');
const email = document.querySelector('#email');
const password = document.querySelector('#password');
const loginBtn = document.querySelector('#loginBtn');

// function setLoading(isLoading){
// loginBtn.disabled = isLoading;
// loginBtn.textContent = isLoading ? "Logging in.." : "Login"; 
// }

form.addEventListener("submit", async (e) => {
    //e.preventDefault();
    const emailText = email.value.trim();
    const passwordText = password.value;

    // setLoading(true);

})