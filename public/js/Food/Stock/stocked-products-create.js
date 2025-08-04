document.addEventListener('DOMContentLoaded', () => {
    console.log(window.LudineApp);
    console.log(window.LudineApp.context);

    let formModified = false;
    let form = document.querySelector('form');
    let id = form.id.value;
    let saveBtn = document.getElementById('save');

    let floor = document.getElementById('stocked_product_floor');
    floor.addEventListener('change', (e) => {
        const location = document.getElementById('stocked_product_location');
        const container = document.getElementById('stocked_product_container').selectedOptions[0];
        if (floor.selectedOptions.length > 0) {
            const floor_context = JSON.parse(floor.selectedOptions[0].dataset.customProperties);

            console.log('/food/stock/containers/floors/get/' + container.dataset.id + '/' + floor_context.id);
            fetch('/food/stock/containers/floors/get/' + container.dataset.id + '/' + floor_context.id, {})
                .then(res => res.json())
                .then(data => {
                    location.max = data.locations.toString();
                    location.value = "1";
                    location.disabled = false
                })
                .catch(err => console.log(err));
        } else {
            location.disabled = true
            location.value = "";
        }
    })

    if (LudineApp.context && LudineApp.context.id !== 'new') {
        const container = document.getElementById('stocked_product_container');
        container.dispatchEvent(new Event('change'));
        // fetch(LudineApp.context.url)
        //     .then(res => res.json())
        //     .then(data => {
        //         const floor = document.getElementById('stocked_product_floor');
        //         Array.from(floor.options).forEach(option => {
        //             console.log(option);
        //             if (option.dataset.customProperties.id === data.floor) {
        //                 floor.selectedIndex = Array.from(floor.options).indexOf(option);
        //                 const choice = window.LudineApp.choicesInstances.get(floor);
        //                 if (choice) {
        //                     choice.setChoiceByValue(option.value)
        //                 }
        //                 // floor.dispatchEvent(new Event('change'));
        //                 console.log('Setting choice to', option.value, 'vs', data.floor);
        //             }
        //         })
        //     })
    }

    form.addEventListener('input', (e) => {
        formModified = true;
    })

    saveBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const tmp = form.stocked_product_product

        console.log({
            id: id,
            product: form.stocked_product_product.selectedOptions.length > 0 ? form.stocked_product_product.selectedOptions[0].dataset.id : "",
            arrivalDate: form.stocked_product_arrivalDate.value,
            expirationDate: form.stocked_product_expirationDate.value,
            stackable: form.stocked_product_stackable.value,
            cool: form.stocked_product_cool.value,
            container: form.stocked_product_container.selectedOptions.length > 0 ? form.stocked_product_container.selectedOptions[0].dataset.id : "",
            floor: form.stocked_product_floor.value,
            location: form.stocked_product_location.value,
        })

        fetch('/food/stock/stocked-products/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                product: form.stocked_product_product.selectedOptions.length > 0 ? form.stocked_product_product.selectedOptions[0].dataset.id : "",
                arrivalDate: form.stocked_product_arrivalDate.value,
                expirationDate: form.stocked_product_expirationDate.value,
                stackable: form.stocked_product_stackable.value,
                cool: form.stocked_product_cool.value,
                container: form.stocked_product_container.selectedOptions.length > 0 ? form.stocked_product_container.selectedOptions[0].dataset.id : "",
                floor: form.stocked_product_floor.value,
                location: form.stocked_product_location.value,
            })
        })
            .then(res => res.json())
            .then(data => {
                console.log('Succès : ', data);
                if (data.missing_fields) {
                    data.missing_fields.forEach(field => {
                        if (field === 'product') {
                            form.stocked_product_product.parentElement.classList.add('invalid');
                        } else if (field === 'arrivalDate') {
                            form.stocked_product_arrivalDate.classList.add('invalid');
                        } else if (field === 'expirationDate') {
                            form.stocked_product_expirationDate.classList.add('invalid');
                        } else if (field === 'stackable') {
                            form.stocked_product_stackable.classList.add('invalid');
                        } else if (field === 'cool') {
                            form.stocked_product_cool.classList.add('invalid');
                        } else if (field === 'container') {
                            form.stocked_product_container.parentElement.classList.add('invalid');
                        } else if (field === 'floor') {
                            form.stocked_product_floor.parentElement.classList.add('invalid');
                        } else if (field === 'location') {
                            form.stocked_product_location.classList.add('invalid');
                        }
                    })
                }
                if (data.stocked_product) {
                    formModified = false;
                    let invalids = document.querySelectorAll('.invalid');
                    invalids.forEach(invalid => {
                        invalid.classList.remove('invalid');
                    })

                    id = data.stocked_product.id
                }
            })
            .catch(err => {
                console.log('Erreur : ', err);
            });
    })

    window.addEventListener('beforeunload', (e) => {
        if (formModified) {
            e.preventDefault();
            alert('Vous avez des modifications non enregistrées')
        }
    })
});