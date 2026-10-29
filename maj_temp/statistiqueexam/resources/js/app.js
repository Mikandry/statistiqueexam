import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const sallesInutilisablesInput = document.querySelector('#salles_inutilisables');
    const salleInputs = document.querySelectorAll('.input-effectif');

    if (sallesInutilisablesInput) {
        const applyInutilisables = () => {
            const inutilisables = sallesInutilisablesInput.value.split(',').map(salle => salle.trim());

            salleInputs.forEach(input => {
                if (inutilisables.includes(input.dataset.salle)) {
                    input.disabled = true;
                    input.classList.add('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                } else {
                    input.disabled = false;
                    input.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                }
            });
        };

        sallesInutilisablesInput.addEventListener('input', applyInutilisables);
        applyInutilisables();
    }
});
