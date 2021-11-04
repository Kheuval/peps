"use strict";
function autoComplete() {
    searchBar.addEventListener('focusout', () => autoCompleteResults.innerHTML = '');
    searchBar.addEventListener('focus', () => autoComplete());
    let url = `/testy/autocomplete/${searchBar.value}`
    fetch(url)
        .then(response => {
            if (!response.ok) {
                return Promise.reject(new Error('Réponse serveur invalide'));
            }
            return response.json();
        })
        .then(products => {
            // Supprimer le contenu de la div englobante.
            autoCompleteResults.innerHTML = '';
            // Créer une sous-div englobante.
            let div = document.createElement('div');
            div.id = 'results';
            // Si valeur saisie mais aucun résultat, l'afficher.
            if (searchBar.value && !products.length) {
                // Créer un p.
                let p = document.createElement('p');
                p.textContent = 'Aucun résultat';
                // Ajouter le p en enfant de la div.
                div.appendChild(p);
                // Ajouter la sous-div en enfant de la div englobante.
                autoCompleteResults.appendChild(div);
            }
            div.style.transformOrigin = 'top';
            div.animate([
                { transform: 'scaleY(0)' },
                { transform: 'scaleY(1)' }
            ], {
                duration: 150,
                iterations: 1
            });
            // Pour chaque produit...
            for (let product of products) {
                // Créer un p.
                let p = document.createElement('p');
                // Définir son contenu.
                p.textContent = `${product.name} (${product.ref}) ${product.price} €`;
                // Ajouter le p en enfant de la div.
                div.appendChild(p);
                // Ajouter la sous-div en enfant de la div englobante.
                autoCompleteResults.appendChild(div);
            }
        });
}