"use strict"

function viewCategoryPopup() {
document.getElementById('categoriesPopup').classList.remove('hidden');
}

function closeCategoriesPopup() {
document.getElementById('categoriesPopup').classList.add('hidden');

}

function editCategoryPopup(id, name, status) {
document.getElementById('editCategoryModal').classList.remove('hidden');

document.getElementById('editCategoryId').value = id;
document.getElementById('editCategoryName').value = name;
document.getElementById('editCategoryStatus').value = status;
}

function closeEditCategoryPopup() {
document.getElementById('editCategoryModal').classList.add('hidden');

}

function deleteCategoryPopup(id, name) {
document.getElementById('deleteCategoryModal').classList.remove('hidden');

document.getElementById('deleteCategoryId').value = id;
document.getElementById('deleteCategoryName').textContent = name;
}

function closeDeleteCategoryPopup() {
document.getElementById('deleteCategoryModal').classList.add('hidden');

}

function addCategoryPopup() {
document.getElementById('addCategoryModal').classList.remove('hidden');
}

function closeAddCategoryPopup() {
document.getElementById('addCategoryModal').classList.add('hidden');

}

function addProductPopup() {
document.getElementById('addProductModal').classList.remove('hidden');
}

function closeAddProductPopup() {
document.getElementById('addProductModal').classList.add('hidden');

}

function editProductPopup(id, name, category, price, stock, status) {
    document.getElementById('editProductModal').classList.remove('hidden');

    document.getElementById('editProductId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductCategory').value = category;
    document.getElementById('editProductPrice').value = price;
    document.getElementById('editProductStock').value = stock;
    document.getElementById('editProductStatus').value = status;

}

function closeEditProductPopup() {
    document.getElementById('editProductModal').classList.add('hidden');
}

function deleteProductPopup(id, name) {
document.getElementById('deleteProductModal').classList.remove("hidden");
document.getElementById('deleteProductName').textContent = name;
document.getElementById('deleteProductId').value = id
}

function closeDeleteProductPopup() {
document.getElementById('deleteProductModal').classList.add("hidden");

}