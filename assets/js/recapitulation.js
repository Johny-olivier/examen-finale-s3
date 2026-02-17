(function () {
    'use strict';

    var boutonActualiser = document.getElementById('bouton-actualiser-recap');
    if (boutonActualiser === null) {
        return;
    }

    var urlAjax = boutonActualiser.getAttribute('data-url-ajax') || '';
    var zoneMessage = document.getElementById('message-ajax-recap');
    var corpsTableau = document.getElementById('corps-tableau-recap-villes');
    var badgeDerniereActualisation = document.getElementById('recap-derniere-actualisation');
    var etatVide = document.getElementById('recap-etat-vide');

    var champsResume = {
        montantBesoinsTotaux: document.getElementById('recap-montant-besoins-totaux'),
        montantBesoinsSatisfaits: document.getElementById('recap-montant-besoins-satisfaits'),
        montantBesoinsRestants: document.getElementById('recap-montant-besoins-restants'),
        tauxSatisfaction: document.getElementById('recap-taux-satisfaction'),
        montantTotalAchats: document.getElementById('recap-montant-total-achats'),
        montantTotalFrais: document.getElementById('recap-montant-total-frais')
    };

    var formateurNombre = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    var formateurDate = new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    function convertirNombre(valeur) {
        var nombre = Number(valeur);
        if (Number.isFinite(nombre) === false) {
            return 0;
        }
        return nombre;
    }

    function formaterMontantAr(valeur) {
        return formateurNombre.format(convertirNombre(valeur)) + ' Ar';
    }

    function formaterPourcentage(valeur) {
        return formateurNombre.format(convertirNombre(valeur)) + ' %';
    }

    function formaterDateHumaine(valeur) {
        var texte = String(valeur || '').trim();
        if (texte === '') {
            return '-';
        }

        var date = new Date(texte.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return texte;
        }

        return formateurDate.format(date);
    }

    function echapperHtml(valeur) {
        return String(valeur)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function afficherMessage(type, message) {
        if (zoneMessage === null) {
            return;
        }

        zoneMessage.className = 'alert';
        zoneMessage.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
        zoneMessage.classList.remove('d-none');
        zoneMessage.textContent = message;
    }

    function remplirTableauVilles(villes) {
        if (corpsTableau === null) {
            return;
        }

        if (Array.isArray(villes) === false || villes.length === 0) {
            corpsTableau.innerHTML = '';
            if (etatVide !== null) {
                etatVide.hidden = false;
            }
            return;
        }

        if (etatVide !== null) {
            etatVide.hidden = true;
        }

        var lignesHtml = villes.map(function (ville) {
            return '<tr>' +
                '<td>' + echapperHtml(ville.region || '') + '</td>' +
                '<td class="fw-semibold">' + echapperHtml(ville.ville || '') + '</td>' +
                '<td class="text-end">' + convertirNombre(ville.total_besoins).toString() + '</td>' +
                '<td class="text-end">' + formaterMontantAr(ville.montant_besoins_totaux) + '</td>' +
                '<td class="text-end">' + formaterMontantAr(ville.montant_besoins_satisfaits) + '</td>' +
                '<td class="text-end">' + formaterMontantAr(ville.montant_besoins_restants) + '</td>' +
            '</tr>';
        }).join('');

        corpsTableau.innerHTML = lignesHtml;
    }

    function mettreAJourDonnees(donnees) {
        var resume = (donnees && typeof donnees === 'object') ? (donnees.resume || {}) : {};
        var villes = (donnees && typeof donnees === 'object') ? (donnees.villes || []) : [];
        var dateActualisation = (donnees && typeof donnees === 'object') ? (donnees.date_actualisation || '') : '';

        if (champsResume.montantBesoinsTotaux !== null) {
            champsResume.montantBesoinsTotaux.textContent = formaterMontantAr(resume.montant_besoins_totaux);
        }
        if (champsResume.montantBesoinsSatisfaits !== null) {
            champsResume.montantBesoinsSatisfaits.textContent = formaterMontantAr(resume.montant_besoins_satisfaits);
        }
        if (champsResume.montantBesoinsRestants !== null) {
            champsResume.montantBesoinsRestants.textContent = formaterMontantAr(resume.montant_besoins_restants);
        }
        if (champsResume.tauxSatisfaction !== null) {
            champsResume.tauxSatisfaction.textContent = formaterPourcentage(resume.taux_satisfaction);
        }
        if (champsResume.montantTotalAchats !== null) {
            champsResume.montantTotalAchats.textContent = formaterMontantAr(resume.montant_total_achats);
        }
        if (champsResume.montantTotalFrais !== null) {
            champsResume.montantTotalFrais.textContent = formaterMontantAr(resume.montant_total_frais);
        }

        if (badgeDerniereActualisation !== null) {
            badgeDerniereActualisation.textContent = 'Derniere actualisation: ' + formaterDateHumaine(dateActualisation);
        }

        remplirTableauVilles(villes);
    }

    function chargerDonneesInitiales() {
        var scriptJson = document.getElementById('donnees-recapitulation-initiales');
        if (scriptJson === null) {
            return;
        }

        var contenu = (scriptJson.textContent || '').trim();
        if (contenu === '') {
            return;
        }

        try {
            var donneesInitiales = JSON.parse(contenu);
            mettreAJourDonnees(donneesInitiales);
        } catch (error) {
            console.error('Erreur parsing donnees initiales recapitulation', error);
        }
    }

    async function actualiserRecapitulation() {
        if (urlAjax === '') {
            afficherMessage('error', 'URL Ajax introuvable.');
            return;
        }

        var texteOriginal = boutonActualiser.innerHTML;
        boutonActualiser.disabled = true;
        boutonActualiser.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Actualisation...';

        try {
            var reponse = await fetch(urlAjax, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            var payload = await reponse.json();
            if (reponse.ok === false || payload.succes !== true) {
                var messageErreur = payload && payload.message ? payload.message : 'Erreur pendant l\'actualisation.';
                throw new Error(messageErreur);
            }

            mettreAJourDonnees(payload.donnees || {});
            afficherMessage('success', 'Recapitulation actualisee avec succes.');
        } catch (error) {
            afficherMessage('error', error instanceof Error ? error.message : 'Erreur pendant l\'actualisation.');
        } finally {
            boutonActualiser.disabled = false;
            boutonActualiser.innerHTML = texteOriginal;
        }
    }

    boutonActualiser.addEventListener('click', function () {
        actualiserRecapitulation();
    });

    chargerDonneesInitiales();
})();
