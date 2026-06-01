"use strict"

function openEditShopPopup(id, name, category, location, email, phone, hours) {
document.getElementById('editShopPopup').classList.remove('hidden');

document.getElementById('editShopId').value = id;
document.getElementById('editShopName').value = name;
document.getElementById('editShopCategory').value = category;
document.getElementById('editShopLocation').value = location;
document.getElementById('editShopEmail').value = email;
document.getElementById('editShopPhone').value = phone;
document.getElementById('editShopHours').value = hours;
}

function closeEdPop() {
document.getElementById('editShopPopup').classList.add('hidden');   

}

function openEditDesc(description) {
    document.getElementById('editShopDescriptionPopup').classList.remove('hidden');

    document.getElementById('description').value = description;

}

function closeDescPop() {
    document.getElementById('editShopDescriptionPopup').classList.add('hidden');

}

function openAddNote() {
    document.getElementById('addNotesPopup').classList.remove('hidden');
}

function closeAddNotePop() {
    document.getElementById('addNotesPopup').classList.add('hidden');
    
}

function editNotePopup(id, title, content) {
    document.getElementById('editNotePopup').classList.remove('hidden');
    document.getElementById('editNoteId').value = id;
    document.getElementById('editNoteTitle').value = title;
    document.getElementById('editNoteContent').value = content;
}

function closeEditNotePop() {
    document.getElementById('editNotePopup').classList.add('hidden');

}

function deleteNotePopup(id, title) {
    document.getElementById('deleteNotePopup').classList.remove('hidden');
    document.getElementById('deleteNoteTitle').textContent = title;
    document.getElementById('deleteNoteId').value = id;

}

function closeDeleteNotePop() {
    document.getElementById('deleteNotePopup').classList.add('hidden');

}