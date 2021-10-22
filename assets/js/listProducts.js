"use strict";

/**
 * Supprime un produit et ses images.
 * 
 * @var idProduct PK du produit.
 * @returns void
 */
async function deleteAll(idProduct) {
    // Si l'utilisateur le confirme, rediriger vers la route adéquate.
    if (confirm("Voulez-vous vraiment supprimer le produit et ses photos ?")) {
        // Solution SYNCHRONE.
        // location = `/product/delete/${idProduct}/all`;
        // Solution ASYNCHRONE.
        const DELETE_URI = `/product/delete/${idProduct}/all`;
        await fetch(DELETE_URI);
        location.reload();
    }
}

/**
 * Supprime les images d'un produit.
 * 
 * @var IdProduct PK du produit.
 * @returns void
 */
async function deleteImg(idProduct) {
    // Si l'utilisateur le confirme, rediriger vers la route adéquate.
    if (confirm("Voulez-vous vraiment supprimer les photos de ce produit ?")) {
        // Solution SYNCHRONE.
        // location = `/product/delete/${idProduct}/img`;
        // Solution ASYNCHRONE.
        const DELETE_URI = `/product/delete/${idProduct}/img`;
        await fetch(DELETE_URI);
        location.reload();
    }
}