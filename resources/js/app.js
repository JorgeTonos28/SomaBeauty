import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.getLocalDateInputValue = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

/** abrir ticket en nueva pestaña de forma segura */
window.openTicketPrintTab = (url) => {
    if (!url) return;

    if (!document.body) {
        window.open(url, '_blank');
        return;
    }

    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.target = '_blank';
    anchor.rel = 'noopener noreferrer';
    anchor.style.display = 'none';

    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
};

/** convertir <select> en buscable */
if (!window.convertSelectToSearchable) {
    window.convertSelectToSearchable = (select) => {
        if (!select || select.dataset.searchableInitialized === 'true') return;

        select.dataset.searchableInitialized = 'true';
        select.classList.add('hidden');

        const wrapper = document.createElement('div');
        wrapper.className = 'relative';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-input w-full mt-1';

        const placeholderText = select.dataset.placeholder || '-- Seleccionar --';
        input.placeholder = placeholderText;

        select._searchInput = input;
        wrapper.appendChild(input);

        const list = document.createElement('ul');
        list.className =
            'absolute z-10 bg-white border border-gray-300 w-full mt-1 max-h-40 overflow-auto hidden';
        wrapper.appendChild(list);

        select.parentNode.insertBefore(wrapper, select);

        const syncFromSelect = () => {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                input.value = selectedOption.text;
            } else {
                input.value = '';
            }
            input.placeholder = placeholderText;
        };

        select._syncSearchInput = syncFromSelect;
        syncFromSelect();

        const show = (filter = '') => {
            list.innerHTML = '';
            const f = filter.toLowerCase();
            Array.from(select.options).forEach((option) => {
                if (!option.value) return;
                if (option.text.toLowerCase().includes(f)) {
                    const li = document.createElement('li');
                    li.textContent = option.text;
                    li.dataset.val = option.value;
                    li.className = 'px-2 py-1 cursor-pointer hover:bg-gray-200';
                    list.appendChild(li);
                }
            });
            list.classList.toggle('hidden', list.children.length === 0);
        };

        input.addEventListener('focus', () => {
            if (!select.value) input.value = '';
            show();
        });

        input.addEventListener('input', () => show(input.value));

        list.addEventListener('mousedown', (event) => {
            const li = event.target.closest('li');
            if (!li) return;
            event.preventDefault();
            input.value = li.textContent;
            select.value = li.dataset.val;
            select.dispatchEvent(new Event('change'));
            list.classList.add('hidden');
        });

        input.addEventListener('blur', () => {
            setTimeout(() => list.classList.add('hidden'), 200);
        });

        select.addEventListener('change', syncFromSelect);
    };
}

window.initSearchableSelects = (root = document) => {
    if (typeof window.convertSelectToSearchable !== 'function') return;
    root.querySelectorAll('select[data-searchable]').forEach((select) => {
        window.convertSelectToSearchable(select);
    });
};

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
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then((r) => r.text())
            .then((html) => {
                this.tableHtml = html;
                if (this.onUpdate) this.onUpdate(html);
            });
    },
    init() { this.fetchTable(); },
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
                if (!confirm('Este ticket tiene más de 6 horas de creado. ¿Seguro que desea editarlo?')) return;
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
    get change() { return (this.paid || 0) - total; },
    formatCurrency(v) {
        return (v).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    async submitForm(event) {
        event.preventDefault();
        if (this.isSubmitting) return;

        this.isSubmitting = true;
        const openPrint = window.openTicketPrintTab ?? ((url) => window.open(url, '_blank'));

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
            try { data = await res.clone().json(); } catch { data = null; }

            if (res.ok) {
                if (data?.print_url) {
                    sessionStorage.setItem('skip_print_ticket', '1');
                    openPrint(data.print_url);
                }
                const redirectUrl = data?.redirect ?? window.location.href;
                window.location = redirectUrl;
                return;
            }

            const message = data?.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data?.message || 'Error inesperado');
            alert(message);
        } catch {
            alert('Error de red');
        } finally {
            this.isSubmitting = false;
        }
    }
}));

Alpine.start();
