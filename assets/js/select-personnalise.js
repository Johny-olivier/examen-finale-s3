(function () {
    'use strict';

    var compteurSelect = 0;
    var selectOuvert = null;

    var fermerSelectOuvert = function () {
        if (!selectOuvert) {
            return;
        }

        selectOuvert.dataset.ouvert = 'false';
        selectOuvert.classList.remove('ouvert-haut');

        var bouton = selectOuvert.querySelector('.select-personnalise-bouton');
        var menu = selectOuvert.querySelector('.select-personnalise-menu');

        if (bouton) {
            bouton.setAttribute('aria-expanded', 'false');
        }

        if (menu) {
            menu.setAttribute('hidden', 'hidden');
        }

        selectOuvert = null;
    };

    var ouvrirSelect = function (conteneur) {
        if (!conteneur) {
            return;
        }

        if (selectOuvert && selectOuvert !== conteneur) {
            fermerSelectOuvert();
        }

        var bouton = conteneur.querySelector('.select-personnalise-bouton');
        var menu = conteneur.querySelector('.select-personnalise-menu');

        if (!bouton || !menu) {
            return;
        }

        conteneur.dataset.ouvert = 'true';
        bouton.setAttribute('aria-expanded', 'true');
        menu.removeAttribute('hidden');

        var rect = conteneur.getBoundingClientRect();
        var hauteurMenu = Math.min(menu.scrollHeight, 250) + 14;
        var espaceBas = window.innerHeight - rect.bottom;
        var espaceHaut = rect.top;

        conteneur.classList.remove('ouvert-haut');
        if (hauteurMenu > espaceBas && espaceHaut > espaceBas) {
            conteneur.classList.add('ouvert-haut');
        }

        selectOuvert = conteneur;

        var optionActive = menu.querySelector('.select-personnalise-option.active:not(:disabled)');
        if (!optionActive) {
            optionActive = menu.querySelector('.select-personnalise-option:not(:disabled)');
        }

        if (optionActive) {
            optionActive.focus();
        }
    };

    var basculerSelect = function (conteneur) {
        if (!conteneur) {
            return;
        }

        if (selectOuvert === conteneur) {
            fermerSelectOuvert();
            return;
        }

        ouvrirSelect(conteneur);
    };

    var synchroniserEtat = function (select, conteneur, bouton, menu) {
        if (!select || !conteneur || !bouton || !menu) {
            return;
        }

        var options = menu.querySelectorAll('.select-personnalise-option');
        options.forEach(function (optionElement) {
            optionElement.classList.remove('active');
        });

        var indexSelectionne = select.selectedIndex;
        var optionSelectionnee = indexSelectionne >= 0 ? select.options[indexSelectionne] : null;

        if (!optionSelectionnee) {
            bouton.textContent = '';
            conteneur.dataset.placeholder = 'true';
            return;
        }

        bouton.textContent = optionSelectionnee.textContent || '';

        var estPlaceholder = optionSelectionnee.value === '';
        conteneur.dataset.placeholder = estPlaceholder ? 'true' : 'false';

        var optionActive = menu.querySelector('[data-index-option="' + String(indexSelectionne) + '"]');
        if (optionActive) {
            optionActive.classList.add('active');
        }
    };

    var construireSelectPersonnalise = function (select) {
        if (!select || select.dataset.selectPersonnaliseActif === 'true') {
            return;
        }

        if (select.multiple || Number(select.size || 0) > 1) {
            return;
        }

        var parent = select.parentNode;
        if (!parent) {
            return;
        }

        compteurSelect += 1;

        var conteneur = document.createElement('div');
        conteneur.className = 'select-personnalise';
        conteneur.dataset.ouvert = 'false';

        parent.insertBefore(conteneur, select);
        conteneur.appendChild(select);

        select.classList.add('select-natif-cache');
        select.dataset.selectPersonnaliseActif = 'true';

        var idMenu = 'menu-select-personnalise-' + String(compteurSelect);

        var bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.className = 'select-personnalise-bouton';
        bouton.setAttribute('aria-haspopup', 'listbox');
        bouton.setAttribute('aria-expanded', 'false');
        bouton.setAttribute('aria-controls', idMenu);

        var menu = document.createElement('div');
        menu.className = 'select-personnalise-menu';
        menu.id = idMenu;
        menu.setAttribute('hidden', 'hidden');

        var liste = document.createElement('ul');
        liste.className = 'select-personnalise-liste';
        liste.setAttribute('role', 'listbox');

        Array.prototype.forEach.call(select.options, function (optionNatif, indexOption) {
            var elementListe = document.createElement('li');

            var boutonOption = document.createElement('button');
            boutonOption.type = 'button';
            boutonOption.className = 'select-personnalise-option';
            boutonOption.dataset.indexOption = String(indexOption);
            boutonOption.dataset.valeur = optionNatif.value;
            boutonOption.textContent = optionNatif.textContent || '';

            if (optionNatif.disabled) {
                boutonOption.disabled = true;
            }

            boutonOption.addEventListener('click', function () {
                if (optionNatif.disabled) {
                    return;
                }

                select.selectedIndex = indexOption;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                conteneur.classList.remove('requis-invalide');
                synchroniserEtat(select, conteneur, bouton, menu);
                fermerSelectOuvert();
                bouton.focus();
            });

            elementListe.appendChild(boutonOption);
            liste.appendChild(elementListe);
        });

        menu.appendChild(liste);

        bouton.addEventListener('click', function () {
            if (select.disabled) {
                return;
            }
            basculerSelect(conteneur);
        });

        bouton.addEventListener('keydown', function (event) {
            if (select.disabled) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                ouvrirSelect(conteneur);
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                basculerSelect(conteneur);
            }
        });

        menu.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                fermerSelectOuvert();
                bouton.focus();
            }

            if (event.key === 'Tab') {
                fermerSelectOuvert();
            }
        });

        select.addEventListener('focus', function () {
            bouton.focus();
        });

        select.addEventListener('change', function () {
            synchroniserEtat(select, conteneur, bouton, menu);
            conteneur.classList.remove('requis-invalide');
        });

        select.addEventListener('invalid', function () {
            conteneur.classList.add('requis-invalide');
        });

        var formulaire = select.form;
        if (formulaire) {
            formulaire.addEventListener('reset', function () {
                window.setTimeout(function () {
                    synchroniserEtat(select, conteneur, bouton, menu);
                    conteneur.classList.remove('requis-invalide');
                }, 0);
            });
        }

        conteneur.appendChild(bouton);
        conteneur.appendChild(menu);

        if (select.disabled) {
            bouton.disabled = true;
        }

        synchroniserEtat(select, conteneur, bouton, menu);
    };

    var initialiserSelectsPersonnalises = function (racine) {
        var contexte = racine && racine.querySelectorAll ? racine : document;
        var selecteur = 'select.form-select:not([data-no-select-personnalise="true"])';
        var selects = [];

        if (contexte.matches && contexte.matches(selecteur)) {
            selects.push(contexte);
        }

        var selectsTrouves = contexte.querySelectorAll(selecteur);
        selectsTrouves.forEach(function (select) {
            selects.push(select);
        });

        selects.forEach(function (select) {
            construireSelectPersonnalise(select);
        });
    };

    document.addEventListener('click', function (event) {
        if (!selectOuvert) {
            return;
        }

        if (selectOuvert.contains(event.target)) {
            return;
        }

        fermerSelectOuvert();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            fermerSelectOuvert();
        }
    });

    window.addEventListener('resize', fermerSelectOuvert);
    window.addEventListener('scroll', function (event) {
        if (!selectOuvert) {
            return;
        }

        var cible = event.target;
        if (cible instanceof Element && selectOuvert.contains(cible)) {
            return;
        }

        fermerSelectOuvert();
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initialiserSelectsPersonnalises(document);
        });
    } else {
        initialiserSelectsPersonnalises(document);
    }

    window.initialiserSelectsPersonnalises = initialiserSelectsPersonnalises;

    if ('MutationObserver' in window && document.body) {
        var observateur = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (noeud) {
                    if (!(noeud instanceof Element)) {
                        return;
                    }
                    initialiserSelectsPersonnalises(noeud);
                });
            });
        });

        observateur.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();
