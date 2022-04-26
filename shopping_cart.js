function cart_update() {
    let storage = localStorage.getItem('localCartStorage');

    if (storage == undefined) storage = {};
    else storage = JSON.parse(storage);

    let totalPrice = 0;
    let cartUpdate = '';

    // clear the previous record
    if (localStorage.getItem('localCartStorage') == '{}' || localStorage.getItem('localCartStorage') == undefined) {
        document.getElementById("total-price").innerHTML = '<p>Total: $' + totalPrice.toString().escapeHTML() + '</p>';
        document.getElementById("cart-list").innerHTML = cartUpdate.escapeHTML();
    }
    
    for (let pid in storage) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "storage.php?pid=" + encodeURIComponent(pid));
        xhr.send();

        // check whether xhr obtain the data successfully or not
        xhr.onreadystatechange = function() {
            // put JSON string into object after checking
            if (xhr.readyState == 4 && xhr.status == 200) var data = JSON.parse(xhr.responseText);

            // further process after xhr got the data successfully
            if (data && typeof data === "object") {
                cartUpdate += '<li>';
                cartUpdate += '<p>' + data[0].NAME.escapeHTML() + '</p>';
                cartUpdate += '<div class="cart-column-right">';
                cartUpdate += '<p>' + data[0].PRICE.escapeHTML() + ' each</p>';
                cartUpdate += '<input type="number" id="' + pid.escapeHTML() + '_quan" min="0" value="' + storage[pid].toString().escapeHTML();
                cartUpdate += '" onchange="quantity_update(' + pid.escapeHTML() + ');">';
                cartUpdate += '<button onclick="button_add(' + pid.escapeHTML() + ');">+</button>';
                cartUpdate += '<button onclick="button_minus(' + pid.escapeHTML() + ');">-</button>';
                cartUpdate += '</div> </li>';
                document.getElementById("cart-list").innerHTML = cartUpdate;

                totalPrice += data[0].PRICE * storage[pid];
                document.getElementById("total-price").innerHTML = '<p>Total: $' + totalPrice.toFixed(2).escapeHTML() + '</p>';
            }
        }  
    }   
}

function add_to_cart(pid) {
    let storage = localStorage.getItem('localCartStorage');

    if (storage == undefined) storage = {};
    else storage = JSON.parse(storage);

    // add to cart if the product have not added yet
    // +1 if the product have already added to the cart
    if (storage[pid] == undefined) storage[pid] = 1;
    else storage[pid] = parseInt(storage[pid]) + 1;

    localStorage.setItem('localCartStorage', JSON.stringify(storage));

    cart_update();
}

// if user input number directly in the box
function quantity_update(pid) {
    let storage = JSON.parse(localStorage.getItem('localCartStorage'));

    let quan = document.getElementById(pid + '_quan').value;
    if (quan <= 0 || quan == '') delete storage[pid];
    else storage[pid] = parseInt(quan);

    localStorage.setItem('localCartStorage', JSON.stringify(storage));
    cart_update();
}

// if user click the button to add/minus
function button_add(pid) {
    let quan = document.getElementById(pid + '_quan').value;
    quan = parseInt(quan) + 1;

    document.getElementById(pid + '_quan').value = quan;
    document.getElementById(pid + '_quan').onchange();
}

function button_minus(pid) {
    let quan = document.getElementById(pid + '_quan').value;
    if (parseInt(quan) > 0) quan = parseInt(quan) - 1;

    document.getElementById(pid + '_quan').value = quan;
    document.getElementById(pid + '_quan').onchange();
}

// output sanitizers
String.prototype.escapeHTML = function() {
	return this.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g, "&quot;").replace(/'/g, "&#039;");;
}

// Passing information to server using AJAX before submitting to PayPal
function cartSubmit() {
    let storage = localStorage.getItem('localCartStorage');
    // check whether the storage is empty
    if (storage == undefined || storage == '{}') return false;
    else storage = JSON.parse(storage);

    let form = document.getElementById("paypalCart");

    // Passing ONLY the pid and quantity (which is localStorage) of every individual product
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "checkout_digest.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send('cart=' + JSON.stringify(storage));

    // Clear the shopping cart
    localStorage.clear();
    cart_update();

    // Insert data into the hidden form for Paypal payment
    xhr.onreadystatechange = function() {
        // put JSON string into object after checking
        if (xhr.readyState == 4 && xhr.status == 200) {
            var formData = JSON.parse(xhr.responseText);

            // inserting into hidden form ([name, pid, price, quan])
            for (let i = 0; i < formData.product.length; i++) {
                let productName = document.createElement("input");
                productName.type = "hidden";
                productName.name = "item_name_" + (i+1);
                productName.value = formData.product[i][0].toString();

                let productID = document.createElement("input");
                productID.type = "hidden";
                productID.name = "item_number_" + (i+1);
                productID.value = formData.product[i][1].toString();

                let productPrice = document.createElement("input");
                productPrice.type = "hidden";
                productPrice.name = "amount_" + (i+1);
                productPrice.value = formData.product[i][2].toString();

                let productQuan = document.createElement("input");
                productQuan.type = "hidden";
                productQuan.name = "quantity_" + (i+1);
                productQuan.value = formData.product[i][3].toString();

                form.appendChild(productName);
                form.appendChild(productID);
                form.appendChild(productPrice);
                form.appendChild(productQuan);
            }

            form.custom.value = formData.custom;
            form.invoice.value = formData.invoice;

            // submit to Paypal
            form.submit();
        }
    }
}