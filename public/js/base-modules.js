window.LudineApp = window.LudineApp || {};
window.LudineApp.context = {}
window.LudineApp.choicesInstances = new Map();
window.LudineApp.actions = {}

document.addEventListener('DOMContentLoaded', async () => {
    // Get context
    const tmpRaw = document.getElementById('context');
    LudineApp.context = JSON.parse(tmpRaw.textContent);
    LudineApp.context.params = Object.fromEntries((new URLSearchParams(window.location.search)).entries());

    let formModified = false;

    // Widget date
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

    // Widget color
    function updateColorFields() {
        const colorFields = document.querySelectorAll('[data-widget="color"]');
        colorFields.forEach(colorField => {
            if (colorField.tagName === 'TD') {
                // Nettoyer les div déjà widgeted dans le cas d'un update
                const divs = colorField.querySelectorAll('div.widget-color');
                if (divs.length > 0) {
                    colorField.textContent = divs[divs.length - 1].style.backgroundColor;
                }

                // Appliquer le widget
                delete colorField.dataset.widget;
                const div = document.createElement('div');
                div.style.width = '50px';
                div.style.height = '16px';
                div.style.borderRadius = '12px';
                div.style.backgroundColor = colorField.textContent;
                div.classList.add('widget-color');
                if (colorField.classList.contains('text-left') ||
                    colorField.classList.contains('text-center') ||
                    colorField.classList.contains('text-right')) {
                    if (colorField.classList.contains('text-left')) {
                        div.style.marginRight = 'auto';
                    } else if (colorField.classList.contains('text-center')) {
                        div.style.margin = 'auto';
                    } else if (colorField.classList.contains('text-right')) {
                        div.style.marginLeft = 'auto';
                    }
                } else {
                    div.style.margin = 'auto';
                }
                colorField.textContent = '';
                colorField.appendChild(div);
            } else if (colorField.tagName === 'INPUT') {
                const parent = colorField.parentElement;
                const defaultColor = colorField.value ? colorField.value : "#FDE388"
                const clone = colorField.cloneNode(true);
                clone.type = "hidden";
                clone.value = defaultColor
                delete clone.dataset.widget;
                parent.insertBefore(clone, parent.firstChild);

                const pickr = Pickr.create({
                    el: colorField,
                    theme: 'nano',
                    default: defaultColor,
                    swatches: [
                        '#FF9C9C', '#F7C698', '#FDE388',
                        '#BBD7F8', '#D9A8CC', '#F8D6C8',
                        '#89E1DB', '#97A6F9', '#FF9ECC',
                        '#B7EDBE', '#E6DBFC'
                    ],
                    components: {
                        preview: false,
                        hue: false,
                        opacity: false,

                        interaction: {
                            hex: false,     // ✅ champ hex
                            rgba: false,   // ❌ champ rgba
                            input: false,   // ✅ input texte
                            save: false,    // ✅ bouton Save
                            clear: false,   // ✅ bouton Clear
                            cancel: false  // ❌ pas besoin ici
                        }
                    }
                });

                pickr.on('save', (color) => {
                    const hex = color.toHEXA().toString();
                    clone.value = hex;
                    console.log(hex);
                });
                pickr.on('change', (color) => {
                    pickr.applyColor(true); // met à jour la preview
                    pickr.hide();           // ferme le panel
                    pickr.options.comparison = false; // empêche l'utilisateur d'annuler
                    pickr._emit('save', pickr.getColor()); // déclenche manuellement l'événement 'save'
                });

                const pickrElement = parent.querySelector('.pickr');
                pickrElement.style.width = '50px';
                pickrElement.style.height = '16px';
                pickrElement.style.borderRadius = '12px';
                if (parent.classList.contains('text-left') ||
                    parent.classList.contains('text-center') ||
                    parent.classList.contains('text-right')) {
                    if (parent.classList.contains('text-left')) {
                        pickrElement.style.marginRight = 'auto';
                    } else if (parent.classList.contains('text-center')) {
                        pickrElement.style.margin = 'auto';
                    } else if (parent.classList.contains('text-right')) {
                        pickrElement.style.marginLeft = 'auto';
                    }
                } else {
                    pickrElement.style.margin = 'auto';
                }
            }
        })
    }
    // updateColorFields()

    // Widget time
    const timeFields = document.querySelectorAll('[data-widget="time"]');
    timeFields.forEach(timeField => {
        flatpickr(timeField, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
        });
    })

    // Widget star
    function createStarSVG(pos, isSelected = false) {
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("width", "22");
        svg.setAttribute("height", "22");
        svg.setAttribute("viewBox", "0 0 22 22");
        svg.setAttribute("fill", "none");
        svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
        svg.classList.add("star");
        if (isSelected) {
            svg.classList.add("selected");
        }
        svg.dataset.pos = pos;

        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("d", "M1.33496 9.33677C1.02171 9.04709 1.19187 8.52339 1.61557 8.47316L7.61914 7.76107C7.79182 7.74059 7.94181 7.63215 8.01465 7.47425L10.5469 1.98446C10.7256 1.59703 11.2764 1.59695 11.4551 1.98439L13.9873 7.47413C14.0601 7.63204 14.2092 7.74077 14.3818 7.76124L20.3857 8.47316C20.8094 8.52339 20.9791 9.04724 20.6659 9.33693L16.2278 13.4419C16.1001 13.56 16.0433 13.7357 16.0771 13.9063L17.255 19.8359C17.3382 20.2544 16.8928 20.5787 16.5205 20.3703L11.2451 17.4166C11.0934 17.3317 10.9091 17.3321 10.7573 17.417L5.48144 20.3695C5.10913 20.5779 4.66294 20.2544 4.74609 19.8359L5.92414 13.9066C5.95803 13.7361 5.90134 13.5599 5.77367 13.4419L1.33496 9.33677Z");
        path.setAttribute("fill", "currentColor");
        path.setAttribute("stroke", "currentColor");
        path.setAttribute("stroke-width", "2");
        path.setAttribute("stroke-linecap", "round");
        path.setAttribute("stroke-linejoin", "round");

        svg.appendChild(path);
        return svg;
    }

    const starFields = document.querySelectorAll('[data-widget="star"]');
    starFields.forEach(starField => {
        if (starField.tagName === 'INPUT') {
            const value = starField.value ? parseInt(starField.value) : 0;
            const max = starField.max ? parseInt(starField.max) : 3;
            const div = document.createElement('div');
            div.classList.add('stars');
            for (let i = max; i > 0; i--) {
                const star = createStarSVG(i, i <= value);
                star.addEventListener('click', () => {
                    Array.from(star.parentElement.children).forEach((child) => {
                        if (child.dataset.pos) {
                            if (child.dataset.pos <= star.dataset.pos && !child.classList.contains('selected')) {
                                child.classList.add('selected');
                            } else if (child.dataset.pos > star.dataset.pos && child.classList.contains('selected')) {
                                child.classList.remove('selected');
                            }
                        }
                    })
                    starField.value = star.dataset.pos;
                })
                div.appendChild(star);
            }
            starField.parentElement.appendChild(div);
            starField.style.display = 'none';
        }
    })

    // Widget tree
    async function initTreeField(treeFields) {
        for (const treeField of treeFields) {
            const model = treeField.id.split('_')[0];
            const field = treeField.id.split('_')[1];
            if (LudineApp.context.trees[field]) {
                const treeData = LudineApp.context.trees[field];

                const container = document.createElement('div');
                container.id = 'table-container';

                const form = document.createElement('form');
                container.appendChild(form);

                const table = document.createElement('table');
                table.classList.add('editable')
                table.id = field

                // Get all fields
                let AllFields = [];
                const treeRelationalFields = [];
                treeData.fields.forEach(field => {
                    if (field.type === 'relational') {
                        treeRelationalFields.push(field);
                        field['display_mode'] = "write";
                        AllFields.push(field);
                    } else {
                        field['display_mode'] = "display,write";
                        AllFields.push(field);
                    }
                })
                for (const treeRelationalField of treeRelationalFields) {
                    const response = await fetch(treeRelationalField.get_meta);
                    const data = await response.json();
                    data.fields.forEach(field => {
                        if (treeRelationalField.display.includes(field.name)) {
                            field['model'] = data.model;
                            field['save_path'] = data.save_path;
                            field['display_mode'] = "display";
                            AllFields.push(field);
                        }
                    })
                }
                // Trier Allfields par séquence
                function sortBySequence(a, b) {
                    const aHasSeq = 'sequence' in a;
                    const bHasSeq = 'sequence' in b;

                    if (aHasSeq && bHasSeq) {
                        return a.sequence - b.sequence; // Tri croissant
                    } else if (aHasSeq) {
                        return -1; // a a une séquence, b non → a avant b
                    } else if (bHasSeq) {
                        return 1;  // b a une séquence, a non → b avant a
                    } else {
                        return 0;  // Aucun des deux n'a de séquence → pas de changement
                    }
                }
                const tmpSortedFields = JSON.parse(JSON.stringify(treeData.fields)); // deep copy
                tmpSortedFields.sort(sortBySequence)
                const sortedFields = [];
                tmpSortedFields.forEach(field => {
                    sortedFields.push(field)
                    if (field.type === 'relational') {
                        const tmpRelationalFields = [];
                        AllFields.forEach(sortedField => {
                            if (sortedField.model === field.name) {
                                tmpRelationalFields.push(sortedField);
                            }
                        })
                        tmpRelationalFields.sort(sortBySequence)
                        sortedFields.push(...tmpRelationalFields);
                    }
                })
                AllFields = sortedFields;

                const colgroup = document.createElement('colgroup');
                AllFields.forEach(field => {
                    const col = document.createElement('col');
                    col.style = `width: ${100 / AllFields.filter(item => item.display_mode.includes('display')).length}%;`;
                    colgroup.appendChild(col);
                });
                table.appendChild(colgroup);

                const thead = document.createElement('thead');
                table.appendChild(thead);

                const tbody = document.createElement('tbody');
                table.appendChild(tbody);

                const theadTr = document.createElement('tr');
                AllFields.filter(item => item.display_mode.includes('display')).forEach(field => {
                    const th = document.createElement('th');
                    th.textContent = field.string ? field.string : field.name;
                    theadTr.appendChild(th);
                });
                thead.appendChild(theadTr);

                try {
                    let response = await fetch(treeData.get_path);
                    response = await response.json();
                    const data = response.data;

                    data.forEach(record => {
                        const tbodyTr = document.createElement('tr');
                        tbodyTr.dataset.id = record.id;

                        AllFields.filter(item => item.display_mode.includes('display')).forEach(field => {
                            const td = document.createElement('td');
                            if (field.model) {
                                const relationalFields = record[field.model];
                                td.textContent = relationalFields[field.name];
                            } else {
                                td.textContent = record[field.name];
                            }
                            td.dataset.type = field.type;
                            td.dataset.name = field.name;
                            tbodyTr.appendChild(td);
                        })

                        tbody.appendChild(tbodyTr);
                    })

                    const tbodyTr = document.createElement('tr');
                    tbodyTr.dataset.id = 'new';

                    AllFields.filter(item => item.display_mode.includes('write')).forEach(field => {
                        const td = document.createElement('td');
                        td.textContent = "Cliquer pour ajouter un nouvel enregistrement"
                        td.dataset.type = field.type;
                        td.dataset.name = field.name;
                        if (field.type === 'relational') {
                            td.dataset.colspan = field.display.length;
                            td.dataset.get_path = field.get_path;
                        }
                        if (AllFields.indexOf(field) !== 0) {
                            td.style.display = 'none';
                        } else {
                            td.colSpan = AllFields.filter(item => item.display_mode.includes('display')).length;
                        }
                        tbodyTr.appendChild(td);
                    })

                    tbody.appendChild(tbodyTr);
                } catch (error) {
                    console.log(`Erreur lors du fetch ${treeData.get_path} :`, error);
                }

                form.appendChild(table);
                container.appendChild(form);
                treeField.appendChild(container);

                treeField.parentElement.classList.add('tree')
            }
        }
    }
    const treeFields = document.querySelectorAll('[data-widget="tree"]');
    await initTreeField(treeFields);

    // Field Text
    const textFields = document.querySelectorAll('textarea');
    textFields.forEach(textField => {
        const autoResize = (el) => {
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        };

        textField.addEventListener('input', () => autoResize(textField));
    })

    // Update page
    if (LudineApp.context) {
        if (LudineApp.context.id !== 'new') {
            if (LudineApp.context.get_path) {
                let object;
                fetch(LudineApp.context.get_path)
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
    const tableContainers = document.querySelectorAll('#table-container');
    for (const tableContainer of tableContainers) {
        const table = tableContainer.querySelector('table');
        if (table) {
            // Cas où Nouveau viendrait d'être cliqué
            if (LudineApp.context.params && LudineApp.context.params.target && LudineApp.context.params.editable) {
                const target = document.querySelector(`#${LudineApp.context.params.target}`);
                if (target) {
                    const tbody = table.querySelector('tbody');
                    if (LudineApp.context.params.editable === 'new') {
                        const trs = tbody.querySelectorAll('tr');
                        const tr = trs[trs.length - 1];
                        const clone = tr.cloneNode(true);
                        clone.dataset.id = LudineApp.context.params.editable;
                        for (const td of clone.querySelectorAll('td')) {
                            // Nettoyer le widget tree
                            if (td.style.display === 'none') {
                                td.style.display = '';
                                if (td.getAttribute('style')?.trim() === '') {
                                    td.removeAttribute('style');
                                }
                            }
                            if (td.colSpan) {
                                td.removeAttribute('colSpan');
                            }

                            if (td.dataset.colspan) {
                                td.colSpan = td.dataset.colspan;
                            }

                            td.textContent = '';
                            if (td.dataset.type) {
                                const div = document.createElement('div');
                                div.classList.add('field');
                                const input = document.createElement(td.dataset.type === 'relational' ? 'select' : 'input');
                                if (td.dataset.type === 'relational') {
                                    input.dataset.widget = 'relational';
                                    input.dataset.placeholder = ' ';
                                    input.classList.add('field');

                                    const records = await fetch(td.dataset.get_path).then(res => res.json());
                                    records.forEach(record => {
                                        const option = document.createElement('option');
                                        option.value = record.id;
                                        option.dataset.id = record.id;
                                        option.dataset.url = td.dataset.get_path + '/' + record.id;
                                        option.dataset.external_id = td.dataset.external_id;
                                        option.textContent = record.name;
                                        input.appendChild(option);
                                    });
                                } else {
                                    input.id = `${LudineApp.context.model}_${td.dataset.name}`;
                                    input.name = td.dataset.name;
                                    input.type = td.dataset.type;
                                }
                                if (td.dataset.widget) {
                                    input.dataset.widget = td.dataset.widget;
                                    delete td.dataset.widget;
                                }
                                div.appendChild(input);
                                td.appendChild(div);
                            }
                        }
                        tbody.appendChild(clone);

                        if (tr.dataset.id && tr.dataset.id === 'new') {
                            tbody.removeChild(tr);
                        }
                    } else {
                        const tr = tbody.querySelector(`tr[data-id="${LudineApp.context.params.editable}"]`);
                        tr.querySelectorAll('td').forEach(td => {
                            const value = td.textContent;
                            td.textContent = '';
                            if (td.dataset.type) {
                                const div = document.createElement('div');
                                div.classList.add('field');
                                const input = document.createElement('input');
                                input.id = `${LudineApp.context.model}_${td.dataset.name}`;
                                input.name = td.dataset.name;
                                input.type = td.dataset.type;
                                input.value = value;
                                if (td.dataset.widget) {
                                    input.dataset.widget = td.dataset.widget;
                                    delete td.dataset.widget;
                                }
                                div.appendChild(input);
                                td.appendChild(div);
                            }
                        })
                    }
                }
            }

            // Gérer le clic sur les records
            if (!table.classList.contains('editable')) {
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
            } else {
                const tbody = table.querySelector('tbody');
                if (tbody) {
                    Array.from(tbody.children).forEach(record => {
                        if (record.dataset.id) {
                            record.addEventListener('click', () => {
                                const url = new URL(window.location.href);
                                url.searchParams.set('target', table.id);
                                url.searchParams.set('editable', record.dataset.id);
                                LudineApp.context.params.target = table.id;
                                LudineApp.context.params.editable = record.dataset.id;
                                if (window.location.href !== url.toString()) {
                                    window.location.href = url.toString();
                                }
                            })
                        }
                    })
                }
            }
        }
    }
    updateColorFields()

    // Widget relational
    const relationalFields = document.querySelectorAll('[data-widget="relational"]');
    relationalFields.forEach(relationalField => {
        const instance = new Choices(relationalField, {
            shouldSort: false,
            searchEnabled: false,
            itemSelectText: '',
            removeItemButton: true,
            callbackOnCreateTemplates: function (template) {
                const defaultItemTemplate = Choices.defaults.templates.item;
                return {
                    item: (classNames, data) => {
                        const element = defaultItemTemplate.call(this, this.config, data, true);

                        const selectEl = this.passedElement.element;
                        const matchingOption = selectEl.querySelector(`option[value="${data.value}"]`);
                        const color = matchingOption?.getAttribute('data-color');

                        if (color) {
                            element.style.backgroundColor = color;
                            element.style.color = '#242529';
                        }

                        return element;
                    }
                };
            }
        });
        if (LudineApp.context) {
            if (LudineApp.context.id === 'new') {
                relationalField.selectedIndex = -1;
            }
        } else {
            relationalField.selectedIndex = -1;
        }

        window.LudineApp.choicesInstances.set(relationalField, instance);

        // --- Portal Choices vers <body> en conservant le contexte de classes (.choices .is-open etc.) ---
        (function portalizeChoicesDropdown() {
            const wrapper = relationalField.closest('.choices'); // conteneur généré par Choices
            let dropdownEl = null;
            let host = null;

            // Miroir des classes du wrapper (is-open, is-flipped, is-focused...) -> host
            const mirrorClasses = () => {
                if (!host) return;
                // garde une classe propre pour cibler en CSS
                host.className = 'choices choices-portal ' + wrapper.className
                    .replace(/\bchoices\b/, '')      // on a déjà "choices"
                    .replace(/\bchoices-portal\b/, '') // au cas où
                    .trim();
            };
            const mo = new MutationObserver(mirrorClasses);
            mo.observe(wrapper, { attributes: true, attributeFilter: ['class'] });

            const reposition = () => {
                if (!host || !dropdownEl) return;
                const r = wrapper.getBoundingClientRect();

                // position par défaut: sous l'input
                let top = r.bottom;

                // si le menu dépasse en bas, on “flip”
                const menuH = dropdownEl.offsetHeight || 280;
                const wouldOverflow = top + menuH > window.innerHeight;
                wrapper.classList.toggle('is-flipped', wouldOverflow);
                host.classList.toggle('is-flipped', wouldOverflow);

                if (wouldOverflow) {
                    top = r.top - menuH;
                }

                host.style.position = 'fixed';
                host.style.left = r.left + 'px';
                host.style.top  = top + 'px';
                host.style.width = r.width + 'px';
                host.style.zIndex = '9999';
            };

            const onShow = () => {
                dropdownEl = wrapper.querySelector('.choices__list--dropdown');
                if (!dropdownEl) return;

                // créer le host une seule fois
                if (!host) {
                    host = document.createElement('div');
                    host.className = 'choices choices-portal is-open';
                    // copie la police pour cohérence visuelle
                    const cs = getComputedStyle(wrapper);
                    host.style.font = cs.font;
                    document.body.appendChild(host);
                } else {
                    host.classList.add('is-open');
                }

                // déplacer le dropdown dans le host
                host.appendChild(dropdownEl);

                // limites raisonnables si très long
                dropdownEl.style.maxHeight = '40vh';
                dropdownEl.style.overflow = 'auto';

                mirrorClasses();
                reposition();

                window.addEventListener('scroll', reposition, true);
                window.addEventListener('resize', reposition);
            };

            const onHide = () => {
                if (!dropdownEl || !host) return;

                // restaurer le DOM
                wrapper.appendChild(dropdownEl);
                dropdownEl.style.maxHeight = '';
                dropdownEl.style.overflow = '';

                host.classList.remove('is-open');

                window.removeEventListener('scroll', reposition, true);
                window.removeEventListener('resize', reposition);
            };

            relationalField.addEventListener('showDropdown', onShow);
            relationalField.addEventListener('hideDropdown', onHide);
        })();

        // const instance = new Choices(relationalField, {
        //     shouldSort: false,
        //     searchEnabled: false,
        //     itemSelectText: '',
        //     removeItemButton: true,
        //     callbackOnCreateTemplates: function (template) { /* ... */ }
        // });
        //
        // /* 👉 AJOUTE ICI (portal Choices vers <body>) */
        // (function portalizeChoicesDropdown() {
        //     const wrapper = relationalField.closest('.choices'); // conteneur rendu par Choices
        //     let dropdownEl = null;
        //
        //     const reposition = () => {
        //         if (!dropdownEl) return;
        //         const r = wrapper.getBoundingClientRect();
        //         dropdownEl.style.left = r.left + 'px';
        //         dropdownEl.style.top  = r.bottom + 'px';  // sous l’input
        //         dropdownEl.style.width = r.width + 'px';
        //     };
        //
        //     const onShow = () => {
        //         // Le dropdown existe maintenant sous wrapper
        //         dropdownEl = wrapper.querySelector('.choices__list--dropdown');
        //         if (!dropdownEl) return;
        //
        //         // Déplacer dans <body> et positionner
        //         document.body.appendChild(dropdownEl);
        //         dropdownEl.style.position = 'fixed';
        //         dropdownEl.style.zIndex = '9999';
        //         dropdownEl.style.maxHeight = '40vh';  // évite les menus trop grands
        //         dropdownEl.style.overflow = 'auto';
        //
        //         reposition();
        //         window.addEventListener('scroll', reposition, true);
        //         window.addEventListener('resize', reposition);
        //     };
        //
        //     const onHide = () => {
        //         if (!dropdownEl) return;
        //         // Replacer dans son wrapper d’origine et nettoyer les styles inline
        //         wrapper.appendChild(dropdownEl);
        //         dropdownEl.removeAttribute('style');
        //         dropdownEl = null;
        //
        //         window.removeEventListener('scroll', reposition, true);
        //         window.removeEventListener('resize', reposition);
        //     };
        //
        //     // Écoute les events émis par Choices sur l’élément <select> d’origine
        //     relationalField.addEventListener('showDropdown', onShow);
        //     relationalField.addEventListener('hideDropdown', onHide);
        // })();
    })

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

    // Save
    let form = document.querySelector('form:not(form:has(table.editable))');
    let editableForms = document.querySelectorAll('form:has(table.editable)');
    let saveBtn = document.getElementById('save');
    saveBtn.addEventListener('click', (e) => {
        e.preventDefault();

        // Save en premier les editables pour ensuite save le modele qui en dépend
        if (editableForms) {
            editableForms.forEach(form => {
                const save_path = form.dataset.save_path ? form.dataset.save_path : LudineApp.context.save_path;
                const fields = form.dataset.fields ? form.dataset.fields : LudineApp.context.fields;
                const model = form.dataset.model ? form.dataset.model : LudineApp.context.model;

                const data = {'id': LudineApp.context.params.editable ? LudineApp.context.params.editable : LudineApp.context.id};
                fields.forEach(field => {
                    if (field.type === 'action') {
                        data[field.name] = LudineApp.actions[field.action] ? LudineApp.actions[field.action]() : null;
                    } else {
                        const formatedFieldName = `${model}_${field.name}`;
                        data[field.name] = form[formatedFieldName] ? form[formatedFieldName].value : null;
                    }
                })

                fetch(save_path, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                }).then(res => res.json())
                .then(data => {
                    if (data.missing_fields) {
                        data.missing_fields.forEach((field) => {
                            const formattedFieldName = `${LudineApp.context.model}_${field}`;
                            form[formattedFieldName].classList.add('invalid');
                        })
                    } else {
                        const target = document.getElementById(LudineApp.context.params.target);
                        const tr = target.querySelector(`tr[data-id="${LudineApp.context.params.editable}"]`);
                        tr.dataset.id = data[LudineApp.context.model].id;
                        tr.querySelectorAll('td').forEach(field => {
                            if (field.querySelectorAll('div.pickr').length > 0) {
                                field.dataset.widget = 'color'
                            }
                            field.textContent = field.querySelector('input').value;
                        })
                        updateColorFields()
                        const url = new URL(window.location);
                        url.search = '';
                        window.history.replaceState({}, document.title, url.toString());
                        delete LudineApp.context.params.target;
                        delete LudineApp.context.params.editable;
                    }
                })
                .catch(err => console.log(err));
            })
        }

        if (form) {
            const data = {'id': LudineApp.context.id ? LudineApp.context.id : ''};
            LudineApp.context.fields.forEach((field) => {
                if (field.type === 'action') {
                    data[field.name] = LudineApp.actions[field.action] ? LudineApp.actions[field.action]() : null;
                } else {
                    const formattedFieldName = `${LudineApp.context.model}_${field.name}`;
                    data[field.name] = form[formattedFieldName] ? form[formattedFieldName].value : null;
                }
            });

            fetch(LudineApp.context.save_path, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(res => res.json())
            .then(data => {
                if (data.missing_fields) {
                    data.missing_fields.forEach((field) => {
                        const formattedFieldName = `${LudineApp.context.model}_${field}`;
                        form[formattedFieldName].classList.add('invalid');
                    })
                }
                if (data[LudineApp.context.model]) {
                    formModified = false;
                    let invalids = document.querySelectorAll('.invalid');
                    invalids.forEach(invalid => {
                        invalid.classList.remove('invalid');
                    })

                    LudineApp.context.id = data[LudineApp.context.model].id;
                }
            })
            .catch(err => console.log(err));
        }
    })

    if (form) {
        form.addEventListener('input', (e) => {
            formModified = true;
        })
    }
    if (editableForms) {
        editableForms.forEach(form => {
            form.addEventListener('input', (e) => {
                formModified = true;
            })
        })
    }

    // window.addEventListener('beforeunload', (e) => {
    //     if (formModified) {
    //         e.preventDefault();
    //         alert('Vous avez des modifications non enregistrées')
    //     }
    // })
})