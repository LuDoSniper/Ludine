document.addEventListener('DOMContentLoaded', () => {
    console.log(window.LudineApp);
    console.log(window.LudineApp.context);

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
});