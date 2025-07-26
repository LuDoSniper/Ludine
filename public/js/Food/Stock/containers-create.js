document.addEventListener('DOMContentLoaded', () => {
    let formModified = false;
    let form = document.querySelector('form');
    let id = form.id.value;
    let saveBtn = document.getElementById('save');
    let kanban = document.querySelector('.kanban #kanban-container');
    let nbCards = parseInt(form.container_nbFloor.value, 10);

    let context = {}

    const meta = form.querySelectorAll('input[type="hidden"]')
    meta.forEach(data => {
        const name = data.getAttribute('name');
        const value = data.value;
        if (name) {
            context[name] = value;
        }
    })

    if (context.nbFloor) {
        nbCards = parseInt(context.nbFloor, 10);
        form.container_nbFloor.value = context.nbFloor;
    }

    form.addEventListener('input', (e) => {
        formModified = true;
    })

    function getFloors() {
        let floors = [];
        kanban.childNodes.forEach((card) => {
            if (card.nodeType !== Node.ELEMENT_NODE) return;

            const descriptionInput = card.querySelector('input#description');
            const locationsInput = card.querySelector('input#locations');

            floors.push({
                id: parseInt(card.id, 10),
                description: descriptionInput ? descriptionInput.value : '',
                locations: locationsInput ? parseInt(locationsInput.value, 10) || 0 : 0
            });
        })

        return floors;
    }

    saveBtn.addEventListener('click', (e) => {
        e.preventDefault();

        const floors = getFloors();

        fetch('/food/stock/containers/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                name: form.container_name.value,
                description: form.container_description.value,
                ref: form.container_ref.value,
                cool: form.container_cool.value,
                nbFloor: form.container_nbFloor.value,
                floors: floors,
            })
        })
            .then(res => res.json())
            .then(data => {
                console.log('Succès : ', data);
                if (data.missing_fields) {
                    data.missing_fields.forEach(field => {
                        if (field === 'name') {
                            form.product_name.classList.add('invalid');
                        } else if (field === 'description') {
                            form.container_description.classList.add('invalid');
                        } else if (field === 'ref') {
                            form.container_ref.classList.add('invalid');
                        } else if (field === 'cool') {
                            form.container_cool.classList.add('invalid');
                        } else if (field === 'nbFloor') {
                            form.container_nbFloor.classList.add('invalid');
                        } else if (field === 'floors') {
                            form.container_nbFloor.classList.add('invalid');
                        }
                    })
                }
                if (data.product) {
                    formModified = false;
                    let invalids = document.querySelectorAll('.invalid');
                    invalids.forEach(invalid => {
                        invalid.classList.remove('invalid');
                    })

                    id = data.product.id
                }
            })
            .catch(err => {
                console.log('Erreur : ', err);
            });
    })

    form.container_nbFloor.addEventListener('change', (e) => {
        let newNb = parseInt(form.container_nbFloor.value, 10);

        if (newNb > 0 && newNb > nbCards) {
            for (let i = 0; i < newNb - nbCards; i++) {
                const card = document.createElement('div');
                card.classList.add('card');
                card.id = (nbCards + i + 1).toString();
                card.style.setProperty('width', '100%');

                const group = document.createElement('div');
                group.classList.add('group');
                group.style.setProperty('max-width', '100%', 'important');

                const title_field = document.createElement('div');
                title_field.classList.add('field');
                const title = document.createElement('label');
                title.textContent = `Étage ${nbCards + i + 1}`;
                title.classList.add('h1');
                title_field.appendChild(title);

                const description_field = document.createElement('div');
                description_field.classList.add('field');
                const description_label = document.createElement('label');
                description_label.textContent = 'Description';
                description_label.setAttribute('for', 'description');
                const description_input = document.createElement('input');
                description_input.type = 'text';
                description_input.id = 'description';
                description_field.appendChild(description_label);
                description_field.appendChild(description_input);

                const locations_field = document.createElement('div');
                locations_field.classList.add('field');
                const locations_label = document.createElement('label');
                locations_label.textContent = 'Nombre d\'emplacement';
                locations_label.setAttribute('for', 'locations');
                const locations_input = document.createElement('input');
                locations_input.type = 'number';
                locations_input.id = 'locations';
                locations_field.appendChild(locations_label);
                locations_field.appendChild(locations_input);

                group.appendChild(title_field);
                group.appendChild(description_field);
                group.appendChild(locations_field);

                card.appendChild(group);

                kanban.appendChild(card);
            }

            nbCards = newNb;
        } else if ( newNb > 0 && newNb < nbCards ) {
            for (let i = nbCards; i > newNb ; i--) {
                const card = document.getElementById(i.toString());
                kanban.removeChild(card);
            }

            nbCards = newNb;
        }
    })

    window.addEventListener('beforeunload', (e) => {
        if (formModified) {
            e.preventDefault();
            alert('Vous avez des modifications non enregistrées')
        }
    })
});