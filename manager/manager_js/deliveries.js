"use script"

function openCouriersModal() {
    document.getElementById('couriersModal').classList.remove('hidden');
}

function closeCouriersModal() {
    document.getElementById('couriersModal').classList.add('hidden');

}

function openDeliveryModal(id, name, surname, status, courier_id) {
    document.getElementById('editDeliveryModal').classList.remove('hidden');

    document.getElementById('editOrderId').value = id;
    document.getElementById('editCustomer').value = `${name} ${surname}`;
    document.getElementById('editStatus').value = status;
    document.getElementById('editCourier').value = courier_id || '';

}

function closeEditDeliveryModal() {
    document.getElementById('editDeliveryModal').classList.add('hidden');

}



function closeAddCourierModal() {
    document.getElementById('addCourierModal').classList.add('hidden');

}

function openAddCourierModal() {
    document.getElementById('addCourierModal').classList.remove('hidden');
}

function openDeleteCourierModal(id, name, surname) {
    document.getElementById('deleteCourierModal').classList.remove('hidden');
    document.getElementById('deleteCourierName').textContent = `${name} ${surname}`;
    document.getElementById('deleteCourierId').value = id;

}

function closeDeleteCourierModal() {
    document.getElementById('deleteCourierModal').classList.add('hidden');

}

function openEditCourierModal(id, name, surname, phone, email, status) {
    document.getElementById('editCourierModal').classList.remove('hidden');

    document.getElementById('editCourierId').value = id;
    document.getElementById('editCourierSurname').value = surname;
    document.getElementById('editCourierName').value = name;
    document.getElementById('editCourierPhone').value = phone;
    document.getElementById('editCourierEmail').value = email;
    document.getElementById('editCourierStatus').value = status;
}

function closeEditCourierModal() {
    document.getElementById('editCourierModal').classList.add('hidden');

}

function openFreeCouriersModal() {
    document.getElementById('freeCouriersModal').classList.remove('hidden');
}

function closeFreeCouriersModal() {
    document.getElementById('freeCouriersModal').classList.add('hidden');
    
}
