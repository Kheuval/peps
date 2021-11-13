function test() {
    let url = `/testy/autocomplete/${autocomplete.value}`
    fetch(url)
        .then(response => {
            if (!response.ok) {
                return Promise.reject(new Error('Réponse serveur invalide'));
            }
            return response.json();
        })
        .then(products => {
            // Supprimer le contenu de la div englobante.
            out.innerHTML = '';
            // Créer une sous-div englobante.
            let div = document.createElement('div');
            div.id = 'results';
            // Si valeur saisie mais aucun résultat, l'afficher.
            if (autocomplete.value && !products.length) {
                // Créer un p.
                let p = document.createElement('p');
                p.textContent = 'Aucun résultat';
                // Ajouter le p en enfant de la div.
                div.appendChild(p);
                // Ajouter la sous-div en enfant de la div englobante.
                out.appendChild(div);
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
            for (product of products) {
                // Créer un p.
                let p = document.createElement('p');
                // Définir son contenu.
                p.textContent = `${product.name} (${product.ref}) ${product.price} €`;
                // Ajouter le p en enfant de la div.
                div.appendChild(p);
                // Ajouter la sous-div en enfant de la div englobante.
                out.appendChild(div);
            }
        });
}