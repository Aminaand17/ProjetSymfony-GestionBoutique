
function togglePopup(show) {
    const popup = document.getElementById('popup');
    popup.classList.toggle('hidden', !show);
}



document.addEventListener('DOMContentLoaded', () => {
    const toggleCheckbox = document.getElementById('toggleAccountCheckbox');
    const toggleTrack = document.getElementById('toggleTrack');
    const toggleThumb = document.getElementById('toggleThumb');
    const toggleLabel = document.getElementById('toggleLabel');
    const accountForm = document.getElementById('accountForm');

    toggleCheckbox.addEventListener('change', () => {
        if (toggleCheckbox.checked) {
            toggleThumb.style.transform = 'translateX(20px)';
            toggleTrack.style.backgroundColor = '#D946EF'; 
            toggleLabel.textContent = 'Oui'; 
            accountForm.classList.remove('hidden'); 
        } else {
            toggleThumb.style.transform = 'translateX(0px)'; 
            toggleTrack.style.backgroundColor = '#D1D5DB'; 
            toggleLabel.textContent = 'Non'; 
            accountForm.classList.add('hidden');
        }
    });

   

    


});

function filterClients(filter) {
    const rows = document.querySelectorAll('#clients-table tbody tr');
    rows.forEach(row => {
        const hasAccount = row.getAttribute('data-has-account') === 'yes';

        if (filter === 'all') {
            row.style.display = '';
        } else if (filter === 'with_account' && !hasAccount) {
            row.style.display = 'none';
        } else if (filter === 'without_account' && hasAccount) {
            row.style.display = 'none';
        } else {
            row.style.display = '';
        }
    });
}

function createUser(clientId) {
    fetch('/admin/clients/create-user', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ client_id: clientId }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.success);
                location.reload();
            } else {
                alert(data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
}



