'use strict'

function deleteCategory(id, name) {
    document.getElementById('deletePopup').classList.remove('hidden');
    // document.getElementById('showCategoryId').innerHTML = `Delete category: ${id}`;
    document.getElementById('deleteCategoryId').value = id;
    document.getElementById('deleteCategoryName').innerHTML = `${name}`;
}

function closeDeletePopup() {
    document.getElementById('deletePopup').classList.add('hidden');

}

function editCategory(id, name, desc, status) {
    document.getElementById('editPopup').classList.remove('hidden');
    
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editCategoryDesc').value = desc;
    document.getElementById('editCategoryStatus').value = status;

}

function closeEditPopup() {
    document.getElementById('editPopup').classList.add('hidden');

}

function addCategory() {
    document.querySelector('#addPopup').classList.remove('hidden');
}

function closeAddPopup() {
    document.querySelector('#addPopup').classList.add('hidden');

}