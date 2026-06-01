"use strict"

function viewOrder(id, name, surname, phone, payment, date, delivery_date, address, status, delivery_status, c_name, c_surname, total, items) {
    document.getElementById('viewOrderModal').classList.remove('hidden');
    document.getElementById('viewOrderId').textContent = `#${id}`;
    document.getElementById('viewOrderCustomer').textContent = `${name} ${surname}`;
    document.getElementById('viewOrderPhone').textContent = phone;
    document.getElementById('viewOrderPayment').textContent = payment;
    document.getElementById('viewOrderDate').textContent = date;
    document.getElementById('viewDeliveryDate').textContent = delivery_date;
    document.getElementById('viewOrderAddress').textContent = address;
    document.getElementById('viewDeliveryStatus').textContent = delivery_status;


    const courier = document.getElementById('viewOrderCourier');
    const status_order = document.getElementById('viewOrderStatus');
    

    if(c_name && c_surname && c_name !== 'null' && c_surname !== 'null') {
        courier.textContent = `${c_name} ${c_surname}`;
    } else {
        courier.textContent = 'Not assigned';
    }

    if(status === 'ready_for_delivery') {
        status_order.textContent = 'ready for delivery';
    } else {
        status_order.textContent = status;
    }


    document.getElementById('viewOrderTotal').textContent = `$${total}`;

    let parse_items = items;
    if(typeof items === 'string') {
        try {
            parse_items = JSON.parse(items);
        } catch(e) {
            console.error(e);
            parse_items = [];
        }
    }

    const itemContainer = document.getElementById('viewOrderItems');
    itemContainer.innerHTML = ``;

    parse_items.forEach(item => {        
        itemContainer.innerHTML += `<tr class="border-t">
                    <td class="px-4 py-3">${item.product_name}</td>
                    <td class="px-4 py-3 text-center">${item.quantity}</td>
                    <td class="px-4 py-3 text-right">$${Number(item.unit_price).toFixed(2)}</td>
                    <td class="px-4 py-3 text-right font-semibold">$${Number(item.total_price).toFixed(2)}</td>
                </tr>
            `;
    });    
}

function closeViewOrderModal() {
    document.getElementById('viewOrderModal').classList.add('hidden');

}

function editOrder(id ,name, surname, status) {
    document.getElementById('editOrderModal').classList.remove('hidden');

    document.getElementById('editOrderId').value = id;
    document.getElementById('editOrderCustomer').textContent = `${name} ${surname}`;
    document.getElementById('editOrderStatus').value = status;


}

function closeEditOrderModal() {
    document.getElementById('editOrderModal').classList.add('hidden');

}