document.addEventListener('DOMContentLoaded', () => {
    const paiementForm = document.querySelector('form');
    const paiementMontantInput = document.querySelector('#montant');
    const paiementList = document.querySelector('tbody');
    const montantTotal = parseFloat(document.querySelector('p strong').nextElementSibling.textContent); // Montant Total
    const montantVerser = document.querySelector('#montantVerser'); 
    const montantRestant = document.querySelector('#montantRestant'); 

    paiementForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const montantPaye = parseFloat(paiementMontantInput.value);

        if (isNaN(montantPaye) || montantPaye <= 0) {
            alert('Veuillez entrer un montant valide.');
            return;
        }

        const totalPaiements = [...paiementList.querySelectorAll('tr')].reduce((acc, row) => acc + parseFloat(row.cells[1].textContent), 0);

        if (totalPaiements + montantPaye > montantTotal) {
            alert('Le montant total des paiements ne doit pas dépasser le montant total de la dette.');
            return;
        }

        const row = document.createElement('tr');
        const datePaiement = new Date().toLocaleDateString('fr-FR');
        row.innerHTML = `
            <td class="py-2 px-4">${datePaiement}</td>
            <td class="py-2 px-4">${montantPaye.toFixed(2)}</td>
        `;
        paiementList.appendChild(row);
        const totalPaiementsMisAJour = totalPaiements + montantPaye;
        montantVerser.textContent = totalPaiementsMisAJour.toFixed(2);
        montantRestant.textContent = (montantTotal - totalPaiementsMisAJour).toFixed(2);

        paiementMontantInput.value = '';
    });
});
