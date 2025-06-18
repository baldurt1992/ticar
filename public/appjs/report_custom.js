Vue.config.devtools = true;
Vue.use(Toasted, {
    duration: 5000,
    position: 'top-center',
    theme: 'toasted'
});

Vue.toasted.register('success', payload => payload.message || 'Éxito', {
    className: 'bg-success text-white',
    duration: 5000
});

Vue.toasted.register('error', payload => payload.message || 'Error', {
    className: 'bg-danger text-white',
    duration: 5000
});
new Vue({
    el: '#app',
    data() {
        return {
            dropdownOpen: false,
            highlight: -1,
            manualEmails: [],
            errors: {
                columns: false,
                format: false,
                custom_day: false,
                custom_time: false,
                emails: false,
            },
            userSearch: '',
            delobj: '',
            spin: false,
            selectedUsers: [],
            emailInput: '',
            custom_report: {
                name: '',
                columns: [],
                filters: {},
                format: 'pdf',
                schedule: 'monthly',
                custom_day: null,
                custom_time: null,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            },
            fieldOptions: [
                { id: 'division', label: 'Sucursal' },
                { id: 'role', label: 'Rol' },
                { id: 'token', label: 'Código' },
                { id: 'name', label: 'Nombre' },
                { id: 'moment_enter', label: 'Entrada' },
                { id: 'moment_exit', label: 'Salida' },
                { id: 'hours', label: 'Horas' },
            ],
            formatSelection: [],
            persons: []
        }
    },
    mounted() {
        this.getUsers();

        document.addEventListener('click', this.handleClickOutside)

        this.fieldOptions.forEach(field => {
            const el = document.getElementById(field.id);
            if (el) {
                el.addEventListener('change', () => {
                    const checked = el.checked;
                    const index = this.custom_report.columns.indexOf(field.id);
                    if (checked && index === -1) {
                        this.custom_report.columns.push(field.id);
                    } else if (!checked && index !== -1) {
                        this.custom_report.columns.splice(index, 1);
                    }
                });
            }
        });

        $('#calendar').datepicker('destroy').datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: true,
            autoclose: true,
            defaultViewDate: new Date(),
            inline: true
        }).on('changeDate', e => {
            this.custom_report.custom_day = parseInt(e.format(0, 'dd'), 10);
        });
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        handleClickOutside(event) {
            const dropdown = this.$el.querySelector('.dropdown-menu');
            const input = this.$el.querySelector('input#user-search');

            if (
                this.dropdownOpen &&
                !dropdown?.contains(event.target) &&
                !input?.contains(event.target)
            ) {
                this.dropdownOpen = false;
            }
        },
        addManualEmail() {
            const correo = this.emailInput.trim();
            if (correo && correo.includes('@') && !this.manualEmails.includes(correo)) {
                this.manualEmails.push(correo);
            }
            this.emailInput = '';
        },

        removeManualEmail(index) {
            this.manualEmails.splice(index, 1);
        },
        closeDropdown() {
            setTimeout(() => {
                this.dropdownOpen = false;
            }, 200);
        },
        getUsers() {
            axios.get('/api/reports/list', {
                params: {
                    start: 0,
                    take: 1,
                    filters: {
                        person: 0,
                        division: 0,
                        rol: 0,
                        dstar: '2025-01-01 00:00:00',
                        dend: '2025-01-01 23:59:59'
                    },
                    orders: {
                        field: 'persons_checks.id',
                        type: 'desc'
                    }
                }
            }).then(res => {
                this.persons = (res.data.persons || []).map(p => ({
                    ...p,
                    name: p.names
                }));
            });
        },
        toggleUser(user) {
            if (user.id === -1) {
                this.selectedUsers = this.persons.filter(p =>
                    !this.selectedUsers.some(s => s.id === p.id)
                );
            } else {
                const idx = this.selectedUsers.findIndex(u => u.id === user.id);
                if (idx >= 0) {
                    this.selectedUsers.splice(idx, 1);
                } else {
                    this.selectedUsers.push(user);
                }
            }
        },
        removeUser(user) {
            this.selectedUsers = this.selectedUsers.filter(u => u.id !== user.id);
        },
        isSelected(user) {
            return this.selectedUsers.some(u => u.id === user.id);
        },
        createCustomReport() {
            this.errors = {
                columns: false,
                format: false,
                custom_day: false,
                custom_time: false,
                emails: false
            };

            let valid = true;

            if (!this.custom_report.columns.length) {
                this.errors.columns = true;
                valid = false;
            }

            this.custom_report.format = this.formatSelection.length === 2 ? 'both' : this.formatSelection[0] || '';
            if (!this.custom_report.format) {
                this.errors.format = true;
                valid = false;
            }

            if (!this.custom_report.custom_day) {
                this.errors.custom_day = true;
                valid = false;
            }

            if (!this.custom_report.custom_time) {
                this.errors.custom_time = true;
                valid = false;
            }

            this.custom_report.emails = [
                ...this.selectedUsers
                    .filter(u => u.email !== 'todos@system.local')
                    .map(u => u.email),
                ...this.manualEmails
            ];

            if (!this.custom_report.emails.length) {
                this.errors.emails = true;
                valid = false;
            }

            if (!valid) {
                this.$toasted.global.error({ message: 'Por favor completa todos los campos requeridos.' });
                return;
            }

            axios.post('/custom-reports', this.custom_report)
                .then(response => {
                    this.$toasted.global.success({ message: response.data.message });
                    this.reset();
                })
                .catch(error => {
                    const msg = error.response?.data?.message || 'Error al guardar el reporte';
                    this.$toasted.global.error({ message: msg });
                });
        },
        reset() {
            this.custom_report = {
                name: '',
                columns: [],
                filters: {},
                format: 'pdf',
                schedule: 'monthly',
                custom_day: null,
                custom_time: null,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            };
            this.formatSelection = [];
            this.manualEmails = [];
            this.emailInput = '';
            this.selectedUsers = [];
            this.userSearch = '';
            this.dropdownOpen = false;
            this.highlight = -1;

            // desmarca los checkboxes de columnas
            const checkboxes = document.querySelectorAll('input[type=checkbox]');
            checkboxes.forEach(cb => cb.checked = false);
        },
        selectUser(user) {
            if (user.id === -1) {
                // Seleccionar todos
                this.selectedUsers = this.persons.filter(p =>
                    !this.selectedUsers.some(s => s.id === p.id)
                );
            } else {
                const idx = this.selectedUsers.findIndex(u => u.id === user.id);
                if (idx >= 0) {
                    this.selectedUsers.splice(idx, 1);
                } else {
                    this.selectedUsers.push(user);
                }
            }
            this.dropdownOpen = false;
            this.highlight = -1;
            this.userSearch = '';
        },

        selectHighlightedUser() {
            if (this.highlight >= 0 && this.highlight < this.filteredUsers.length) {
                this.selectUser(this.filteredUsers[this.highlight]);
            }
        },


    },
    computed: {
        filteredUsers() {
            const q = (this.userSearch || '').toLowerCase();
            let base = this.persons.filter(u =>
                (u.name || '').toLowerCase().includes(q) ||
                (u.email || '').toLowerCase().includes(q)
            );

            if (q === '' || 'todos'.includes(q)) {
                base.unshift({ id: -1, name: 'TODOS', email: 'todos@system.local' });
            }

            return base;
        }

    }
});
