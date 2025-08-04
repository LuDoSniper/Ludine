window.LudineApp = window.LudineApp || {};
window.LudineApp.context = {}
window.LudineApp.choicesInstances = new Map();

document.addEventListener('DOMContentLoaded', () => {
    const relationalFields = document.querySelectorAll('[data-widget="relational"]');

    relationalFields.forEach(relationalField => {
        const instance = new Choices(relationalField, {
            shouldSort: false,
            searchEnabled: false,
            itemSelectText: ''
        });
        if (LudineApp.context) {
            if (LudineApp.context.id === 'new') {
                relationalField.selectedIndex = -1;
            }
        } else {
            relationalField.selectedIndex = -1;
        }

        window.LudineApp.choicesInstances.set(relationalField, instance);
    })

    const dateFields = document.querySelectorAll('[data-widget="date"]');
    dateFields.forEach(dateField => {
        new AirDatepicker('#' + dateField.id, {
            autoclose: true,
            dateFormat: 'dd/MM/yyyy',
            locale: {
                days: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                daysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                daysMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                months: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
                monthsShort: ['Janv', 'Févr', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
                today: 'Aujourd’hui',
                clear: 'Effacer'
            }
        });
    })

    // Get context
    const metas = document.querySelectorAll('[data-context]');
    metas.forEach(meta => {
        LudineApp.context = {
            ...LudineApp.context,
            ...JSON.parse(meta.dataset.context)
        };
    })

    // Update page
    if (LudineApp.context) {
        if (LudineApp.context.id !== 'new') {
            if (LudineApp.context.url) {
                let object;
                fetch(LudineApp.context.url)
                    .then(res => res.json())
                    .then(data => {
                        // object = data;
                        // const computedFields = document.querySelectorAll('[data-computed]');
                        // computedFields.forEach(field => {
                        //     const fieldTarget = field.dataset.computed.split('.')[1];
                        //     const modelTarget = document.querySelectorAll('[data-external_id="' + field.dataset.computed.split('.')[0] + '"');
                        //
                        //     if (field.target) {
                        //         modelTarget.forEach(model => {
                        //             if (model.dataset.id === )
                        //         })
                        //     }
                        // })

                        document.querySelectorAll('[data-computed]').forEach(field => {
                            const target = field.dataset.computed.split('.')[0];
                            document.querySelectorAll(`[data-external_id="${target}"]`)[0].parentElement.dispatchEvent(new Event('change'));
                        });
                    })
            }
        }
    }

    // Click on kanban card
    const kanban = document.querySelector('.kanban');
    if (kanban) {
        const cardsContainer = document.querySelector('#kanban-container');
        if (cardsContainer) {
            cardsContainer.childNodes.forEach(card => {
                card.addEventListener('click', () => {
                    // Get context via metadatas
                    const meta = card.querySelectorAll('input[type="hidden"]');
                    meta.forEach(data => {
                        const name = data.getAttribute('name');
                        const value = data.value;
                        if (name) {
                            window.LudineApp.context[name] = value;
                        }
                    })

                    if (window.LudineApp.context.url) {
                        window.location.href = window.LudineApp.context.url;
                    }
                })
            })
        }
    }

    // Click on table record
    const tableContainer = document.querySelector('#table-container');
    if (tableContainer) {
        const tbody = tableContainer.querySelector('tbody');
        if (tbody) {
            tbody.childNodes.forEach(record => {
                record.addEventListener('click', () => {
                    const meta = record.querySelectorAll('input[type="hidden"]');
                    meta.forEach(data => {
                        const name = data.getAttribute('name');
                        const value = data.value;
                        if (name) {
                            window.LudineApp.context[name] = value;
                        }
                    })

                    if (window.LudineApp.context.url) {
                        window.location.href = window.LudineApp.context.url;
                    }
                })
            })
        }
    }

    relationalFields.forEach(field => {
        field.addEventListener('change', (e) => {
            if (field.selectedIndex !== -1) {
                const option = field.options[field.selectedIndex];
                const local_context = {
                    id: option.dataset.id,
                    url: option.dataset.url,
                    externalID: option.dataset.external_id,
                }

                const computedFields = document.querySelectorAll(`[data-computed]`);
                computedFields.forEach(field => {
                    const targetModel = field.dataset.computed.split('.')[0];
                    const targetField = field.dataset.computed.split('.')[1];
                    if (targetModel === local_context.externalID) {
                        fetch(local_context.url, {})
                            .then(res => res.json())
                            .then(data => {
                                const value = data[targetField];

                                if (field.tagName.toLowerCase() === 'select') {
                                    const choices = window.LudineApp.choicesInstances.get(field);
                                    if (choices) {
                                        const formattedChoices = value.map(item => ({
                                            value: item.id?.toString() ?? item.name ?? item.toString(),
                                            label: item.name ?? item.description ?? item.id?.toString() ?? item.toString(),
                                            customProperties: {
                                                id: item.id?.toString(),
                                                url: local_context.url,
                                                externalID: local_context.externalID
                                            }
                                        }));

                                        field.selectedIndex = -1;
                                        while (field.options.length > 0) {
                                            field.remove(0);
                                        }
                                        field.dispatchEvent(new Event('change', { bubbles: true }));

                                        field.disabled = true;
                                        choices.disable();

                                        choices.clearStore();
                                        choices.setChoices(formattedChoices, 'value', 'label', true);

                                        field.disabled = false;
                                        choices.enable();
                                    }
                                } else {
                                    field.value = value;
                                }
                            })
                            .catch(err => console.log(err));
                    }
                })
            }
        })
    })
})