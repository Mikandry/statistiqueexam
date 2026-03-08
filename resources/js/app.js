import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const sallesInutilisablesInput = document.querySelector('#salles_inutilisables');
    const salleInputs = document.querySelectorAll('.input-effectif');

    if (sallesInutilisablesInput) {
        sallesInutilisablesInput.addEventListener('input', () => {
            const inutilisables = sallesInutilisablesInput.value.split(',').map(salle => salle.trim());

            salleInputs.forEach(input => {
                if (inutilisables.includes(input.dataset.salle)) {
                    input.disabled = true;
                } else {
                    input.disabled = false;
                }
            });
        });
    }
});
