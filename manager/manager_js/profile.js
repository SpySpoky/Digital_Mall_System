"use strict"

const form = document.getElementById('PassForm');

function validPass(password1, password2) {
        if (password1 !== password2) {
            alert("Passwords do not match!")
        }
    }

    form.addEventListener("submit", async (e) =>{
    //e.preventDefault();

    const password = document.getElementById('new_pass').value.trim();
    const confirm_password = document.getElementById('conf_pass').value.trim();

    validPass(password, confirm_password);
})