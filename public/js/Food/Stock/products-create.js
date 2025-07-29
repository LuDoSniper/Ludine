document.addEventListener('DOMContentLoaded', () => {
    let formModified = false;
    let form = document.querySelector('form');
    let id = form.id.value;
    let saveBtn = document.getElementById('save');

    form.addEventListener('input', (e) => {
        formModified = true;
    })

    saveBtn.addEventListener('click', (e) => {
        e.preventDefault();
        fetch('/food/stock/products/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                name: form.product_name.value,
                description: form.product_description.value,
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
                        form.product_description.classList.add('invalid');
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

    window.addEventListener('beforeunload', (e) => {
        if (formModified) {
            e.preventDefault();
            alert('Vous avez des modifications non enregistrées')
        }
    })
});