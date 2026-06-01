'use strict'

const form = document.getElementById('signupForm'),
    nameInput = document.getElementById('nameInput'),
    surnameInput = document.getElementById('surnameInput'),
    countryInput = document.getElementById('countryInput'),
    cityInput = document.getElementById('cityInput'),
    streetInput = document.getElementById('streetInput'),
    emailInput = document.getElementById('emailInput'),
    passwordInput = document.getElementById('passwordInput'),
    confirmPassInput = document.getElementById('confirmPassInput'),
    signupBtn = document.getElementById('signupBtn');

    function validPass(password1, password2) {
        if (password1 !== password2) {
            alert("Passwords do not match!")
        }
    }

form.addEventListener("submit", async (e) =>{
    //e.preventDefault();

    const name = nameInput.value.trim();
    const surname = surnameInput.value.trim();
    const country = countryInput.value.trim();
    const city = cityInput.value.trim();
    const street = streetInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPassInput.value;

    validPass(password, confirmPassword);
})
