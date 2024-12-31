document.addEventListener('alpine:init', () => {

    Alpine.store('darkMode', {
        on: Alpine.$persist(true).as('darkMode'),

        toggle() {
            this.on = ! this.on
        }
    })
})
