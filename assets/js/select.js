function select() {
    outy.innerHTML = '';
    for (category of categories) {
        if (category.idCategory === parseInt(selectCategories.value, 10)) {
            let selectProducts = document.createElement('select');
            selectProducts.id = "selectProducts";
            for (product of category.products) {
                selectProducts.options[selectProducts.options.length] = new Option(product.name, product.idProduct);
            }
            outy.appendChild(selectProducts);
        }
    }
}