#!/usr/bin/env bash
set -euo pipefail

afficher_aide() {
    cat <<'EOF'
Usage:
  ./git.sh push-initial <url_repo_github>
  ./git.sh nouvelle-feature <nom_feature_sans_prefixe>
  ./git.sh fusion-dev <branche_feature>
  ./git.sh release-main "<message_release>"

Exemples:
  ./git.sh push-initial https://github.com/compte/projet.git
  ./git.sh nouvelle-feature 10-amelioration-dashboard
  ./git.sh fusion-dev feature/10-amelioration-dashboard
  ./git.sh release-main "release(main): dashboard v2"
EOF
}

commade="${1:-}"

if [[ -z "${commade}" ]]; then
    afficher_aide
    exit 1
fi

case "${commade}" in
    push-initial)
        url_repo="${2:-}"
        if [[ -z "${url_repo}" ]]; then
            echo "Erreur: URL du repo GitHub manquante."
            exit 1
        fi

        if git remote get-url origin >/dev/null 2>&1; then
            git remote set-url origin "${url_repo}"
        else
            git remote add origin "${url_repo}"
        fi

        git push -u origin main DEV
        git push origin \
            feature/01-sql-schema-et-seeds \
            feature/02-socle-technique \
            feature/03-distribution-dons \
            feature/04-stock-bngrc \
            feature/05-dashboard \
            feature/06-besoins \
            feature/07-dons \
            feature/08-integration-routes-ui \
            feature/09-script-git-workflow
        ;;

    nouvelle-feature)
        nom_feature="${2:-}"
        if [[ -z "${nom_feature}" ]]; then
            echo "Erreur: nom de feature manquant."
            exit 1
        fi

        git checkout DEV
        git checkout -b "feature/${nom_feature}"
        echo "Branche creee: feature/${nom_feature}"
        ;;

    fusion-dev)
        branche_feature="${2:-}"
        if [[ -z "${branche_feature}" ]]; then
            echo "Erreur: branche feature manquante."
            exit 1
        fi

        git checkout DEV
        git merge --no-ff "${branche_feature}" -m "merge(${branche_feature}): integration ${branche_feature}"
        ;;

    release-main)
        message_release="${2:-release(main): livraison depuis DEV}"
        git checkout main
        git merge --no-ff DEV -m "${message_release}"
        ;;

    *)
        afficher_aide
        exit 1
        ;;
esac

