'use strict'

let current_shop_id = null;
let currentManagerId = null;
let currentManagerName = null;


function editShop(id, shop_name, manager_id, name, surname, category, location, status) {

    current_shop_id = id;
    currentManagerId = manager_id;
    currentManagerName = `${name} ${surname}`;


    document.getElementById('editShopId').value = current_shop_id;
    document.getElementById('editShopName').value = shop_name;
    document.getElementById('editShopOwner').value = currentManagerName;
    document.getElementById('editShopCategory').value = category;
    document.getElementById('editShopLocation').value = location;
    document.getElementById('editShopStatus').value = status;

    document.getElementById('editPopup').classList.remove('hidden');
}

function openChangeManagerPopup() {
    
    if(current_shop_id) {
        document.getElementById('changeShopId').value = current_shop_id;
        document.getElementById('changeOldManagerId').value = currentManagerId;
        document.getElementById('currentManagerName').value = currentManagerName;
        // document.getElementById('newManagerSelect').value = '';
        
    }
    document.getElementById('changeManagerPopup').classList.remove('hidden');

}

function closeChangeManagerPopup() {
    document.getElementById('changeManagerPopup').classList.add('hidden');

}

function closeEditPopup() {
    document.getElementById('editPopup').classList.add('hidden');
}

function deleteShop(id, name) {
    document.getElementById('showShopId').innerHTML = `Delete Shop: ${id}`;
    document.getElementById('deleteShopId').value = id;
    document.getElementById('deleteShopName').textContent = name;
    document.getElementById('deletePopup').classList.remove('hidden');
}

function closeDeletePopup() {
    document.getElementById('deletePopup').classList.add('hidden');
}
