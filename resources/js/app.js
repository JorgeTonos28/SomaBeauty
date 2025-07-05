import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('filterTable', (url, extra = {}) => ({
    ...extra,
    tableHtml: '',
    fetchTable() {
        const form = this.$refs.form;
        const params = new URLSearchParams(new FormData(form));
        params.set('pending', this.pending);
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
    },
    openCancelModal() {
        if (!this.selectedCreated) return;
        const created = new Date(this.selectedCreated);
        const diffHours = (Date.now() - created.getTime()) / 3600000;
        if (diffHours > 6) {
            this.$dispatch('open-modal', 'cancel-error');
        } else {
            this.$dispatch('open-modal', 'cancel-' + this.selected);
        }
    },
    openEdit() {
        if (!this.selectedCreated || !this.selected) return;
        const created = new Date(this.selectedCreated);
        const diffHours = (Date.now() - created.getTime()) / 3600000;
        if (diffHours > 6) {
            if (this.role === 'admin') {
                if (!confirm('Este ticket tiene más de 6 horas de creado. ¿Seguro que desea editarlo?')) {
                    return;
                }
            } else {
                alert('No se puede editar un ticket con más de 6 horas de creado.');
                return;
            }
        }
        window.location = `${this.editBase}/${this.selected}/edit`;
    }
}));

Alpine.data('payForm', (total) => ({
    paid: total,
    method: 'efectivo',
    get change() {
        return (this.paid || 0) - total;
    },
    formatCurrency(v) {
        return (v).toLocaleString('es-DO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}));

Alpine.start();
