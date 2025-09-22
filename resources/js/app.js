import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('filterTable', (url, extra = {}) => ({
    ...extra,
    tableHtml: '',
    fetchTable() {
        const form = this.$refs.form;
        const startInput = form.querySelector('[name="start"]');
        const endInput = form.querySelector('[name="end"]');
        if (startInput && endInput) {
            endInput.min = startInput.value;
            startInput.max = endInput.value;
            if (startInput.value && endInput.value && startInput.value > endInput.value) {
                alert('La fecha de inicio no puede ser mayor a la fecha de término.');
                return;
            }
        }
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
                if (this.onUpdate) {
                    this.onUpdate(html);
                }
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

Alpine.data('payForm', (total, action) => ({
    paid: total,
    method: 'efectivo',
    action,
    isSubmitting: false,
    printFeatures: 'width=420,height=720,noopener,noreferrer',
    get change() {
        return (this.paid || 0) - total;
    },
    formatCurrency(v) {
        return (v).toLocaleString('es-DO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },
    async submitForm(event) {
        event.preventDefault();
        if (this.isSubmitting) {
            return;
        }

        this.isSubmitting = true;
        let printWindow = window.open('', '_blank', this.printFeatures);

        try {
            const form = event.target;
            const res = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            let data = null;
            try {
                data = await res.clone().json();
            } catch (error) {
                data = null;
            }

            if (res.ok) {
                if (data?.print_url && printWindow) {
                    sessionStorage.setItem('skip_print_ticket', '1');
                    printWindow.location = data.print_url;
                    printWindow.focus();
                } else if (printWindow) {
                    printWindow.close();
                    printWindow = null;
                }
                const redirectUrl = data?.redirect ?? window.location.href;
                window.location = redirectUrl;
                return;
            }

            if (printWindow) {
                printWindow.close();
                printWindow = null;
            }

            const message = data?.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data?.message || 'Error inesperado');
            alert(message);
        } catch (error) {
            if (printWindow) {
                printWindow.close();
                printWindow = null;
            }
            alert('Error de red');
        } finally {
            this.isSubmitting = false;
        }
    }
}));

Alpine.start();
