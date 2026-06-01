'use strict'


function delete_user(id , name, surname) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('showUserId').innerHTML = `Delete user: ${id}`;
    document.getElementById('deleteUserName').innerHTML = `${name} ${surname}`;
    
    document.getElementById('deletePopup').classList.remove('hidden');
}

function closeDeletePopup() {
    document.getElementById('deletePopup').classList.add('hidden');
}

function edit_user(id, name, surname, phone, email, role, address, status) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editSurname').value = surname;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;
    document.getElementById('editAddress').value = address;
    document.getElementById('editStatus').value = status;

    document.querySelector('#editPopup').classList.remove('hidden');


}

function closeEditPopup() {
    document.querySelector('#editPopup').classList.add('hidden');
}

function add_user() {
       document.querySelector('#addPopup').classList.remove('hidden');
}

function closeAddPopup() {
    document.querySelector('#addPopup').classList.add('hidden');
}