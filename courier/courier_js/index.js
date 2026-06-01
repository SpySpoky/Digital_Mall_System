"use script"

function openEditDeliveryPopup(id, name, surname, address, status, date) {
    document.getElementById("editDeliveryPopup").classList.remove("hidden")
    document.getElementById("editOrderDisplayId").value = id;
    document.getElementById("editDeliveryCustomer").value = `${name} ${surname}`;
    document.getElementById("editDeliveryAddress").value = address;
    document.getElementById('editDeliveryDate').value = date;
    document.getElementById("editDeliveryStatus").value = status;
    document.getElementById('editDeliveryId').value = id;
}

function closeEditDeliveryPopup() {
    document.getElementById("editDeliveryPopup").classList.add("hidden")

}