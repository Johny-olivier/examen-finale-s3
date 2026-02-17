(function () {
    'use strict';

    var lightbox = document.getElementById('lightbox-details-ville');
    var contenuLightbox = document.getElementById('contenu-lightbox-ville');
    var titreLightbox = document.getElementById('titre-lightbox-ville');

    if (!lightbox || !contenuLightbox || !titreLightbox) {
        return;
    }

    var boutons = document.querySelectorAll('.bouton-voir-details-ville');
    var boutonFermer = lightbox.querySelector('[data-fermer-lightbox]');

    var fermerLightbox = function () {
        lightbox.setAttribute('hidden', 'hidden');
        document.body.classList.remove('lightbox-ouvert');
        contenuLightbox.innerHTML = '';
    };

    var ouvrirLightbox = function (idVille, libelleVille) {
        var template = document.getElementById('template-ville-' + idVille);
        if (!template) {
            return;
        }

        contenuLightbox.innerHTML = '';
        contenuLightbox.appendChild(template.content.cloneNode(true));
        if (typeof window.initialiserSelectsPersonnalises === 'function') {
            window.initialiserSelectsPersonnalises(contenuLightbox);
        }
        titreLightbox.textContent = 'Details - ' + libelleVille;

        lightbox.removeAttribute('hidden');
        document.body.classList.add('lightbox-ouvert');
    };

    boutons.forEach(function (bouton) {
        bouton.addEventListener('click', function () {
            var idVille = bouton.getAttribute('data-id-ville');
            var libelleVille = bouton.getAttribute('data-libelle-ville') || 'Ville';
            if (!idVille) {
                return;
            }
            ouvrirLightbox(idVille, libelleVille);
        });
    });

    if (boutonFermer) {
        boutonFermer.addEventListener('click', fermerLightbox);
    }

    lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            fermerLightbox();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !lightbox.hasAttribute('hidden')) {
            fermerLightbox();
        }
    });
})();
