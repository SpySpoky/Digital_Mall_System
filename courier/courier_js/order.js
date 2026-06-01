
function openUpdateDeliveryPopup(orderId, name, surname, address, date, status) {
    document.getElementById("updateOrderId").value = "#" + orderId;
    document.getElementById("updateCustomer").value = `${name} ${surname}`;
    document.getElementById("updateAddress").value = address;
    document.getElementById("updateDate").value = date;
    document.getElementById("updateDeliveryStatus").value = status;
    document.getElementById("updateDeliveryId").value = orderId;

    document.getElementById("updateDeliveryPopup").classList.remove("hidden");
}

function closeUpdateDeliveryPopup() {
    document.getElementById("updateDeliveryPopup").classList.add("hidden");
}
