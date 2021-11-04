"use strict";
function select() {
    out.innerHTML = '';
    for (let category of categories) {
        if (category.idCategory === parseInt(idCategory.value, 10)) {
            let label = document.createElement('label');
            label.textContent = "Produits : ";
            let selectProducts = document.createElement('select');
            selectProducts.id = "selectProducts";
            for (let product of category.products) {
                selectProducts.options[selectProducts.options.length] = new Option(product.name, product.idProduct);
            }
            out.appendChild(label);
            out.appendChild(selectProducts);
        }
    }
}