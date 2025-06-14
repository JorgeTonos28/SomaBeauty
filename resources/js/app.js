import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('filterTable', (url, extra = {}) => ({
    ...extra,
    tableHtml: '',
    fetchTable() {
        const form = this.$refs.form;
        const params = new URLSearchParams(new FormData(form));
        params.append('ajax', '1');
        fetch(`${url}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(r => r.text())
            .then(html => {
                this.tableHtml = html;
            });
    },
    init() {
        this.fetchTable();
    }
}));

Alpine.start();
