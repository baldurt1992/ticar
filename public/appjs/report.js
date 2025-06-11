Vue.config.devtools = true;

Vue.use(Toasted);

new Vue({
    el: '#app',
    data() {
        return {
            totales_tokens: {},
            tokens_finalizados: [],
            file: null,
            formData: 0,
            delobj: '',
            erros: {},
            title: '',
            spin: false,
            act: 'post',
            lists: [],
            views: {
                list: true,
                new: false,
                details: false,
            },
            unlookview: false,
            importview: false,
            listfield: [{ name: 'Fecha', type: 'date', field: 'persons_checks.moment' }, { name: 'Motivo', type: 'select', field: 'persons_checks.motive_id' }],
            fieldtype: 'text',
            filters: {
                division: 0,
                rol: 0,
                person: 0,
                dstar: moment().hour(8).minute(40),
                dend: moment().hour(8).add(9, 'hour').minute(10),
            },
            orders: {
                field: 'persons_checks.id',
                type: 'asc'
            },
            pager: {
                page: 1,
                recordpage: 12
            },
            totalpage: 0,
            persons: [],
            rols: [],
            divisions: []
        }
    },
    watch: {
        'filters.division': function () {
            this.pager.page = 1;
            this.getlist()
        },
        'filters.rol': function () {
            this.pager.page = 1;
            this.getlist()
        },
        'filters.person': function () {
            this.pager.page = 1;
            this.getlist()
        },
        'filters.dstar': function () {
            this.pager.page = 1;
            this.getlist()
        },
        'filters.dend': function () {
            this.pager.page = 1;
            this.getlist()
        },
    },
    mounted() {
        $('input[name="datetimes"]').daterangepicker({
            timePicker: true,
            opens: 'left',
            cancelClass: "btn-danger",
            startDate: moment().startOf('month').startOf('day'),
            endDate: moment().endOf('day'),
            locale: {
                applyLabel: "Aplicar",
                cancelLabel: "Anular",
                fromLabel: "de",
                toLabel: "a",
                customRangeLabel: "personalisar",
                weekLabel: "S",
                daysOfWeek: ["Do", "Lu", "Mar", "Mir", "Jue", "Vi", "Sa"],
                monthNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
                format: 'D-M-Y hh:mm A'
            }
        }, (start, end, label) => {
            this.filters.dstar = start.format('YYYY-MM-DD HH:mm:ss');
            this.filters.dend = end.format('YYYY-MM-DD HH:mm:ss');
        });

        const picker = $('input[name="datetimes"]').data('daterangepicker');

        this.filters.dstar = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
        this.filters.dend = picker.endDate.format('YYYY-MM-DD HH:mm:ss');

        this.formData = new FormData();

        this.getlist();
    },

    methods: {
        totalHoras(group) {
            let total = 0;
            group.forEach(item => {
                if (item.hours) {
                    const [h, m] = item.hours.split(':').map(n => parseInt(n));
                    total += h * 60 + m;
                }
            });
            const horas = Math.floor(total / 60);
            const minutos = total % 60;
            return `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}`;
        },

        formatFecha(fecha) {
            return fecha ? moment(fecha).format('DD/MM/YY HH:mm') : '-';
        },
        getpdf() {
            this.spin = true;

            axios.post(urldomine + 'api/reports/pdf', { filters: this.filters }, {
                responseType: 'blob'
            })
                .then(response => {
                    this.spin = false;
                    const contentType = response.headers['content-type'];
                    if (!contentType.includes('application/pdf')) {
                        const reader = new FileReader();
                        reader.onload = () => {
                            this.$toasted.show(reader.result || 'Error desconocido', toast_options);
                        };
                        reader.readAsText(response.data);
                        return;
                    }
                    const blob = new Blob([response.data], { type: 'application/pdf' });
                    const url = URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'reporte.pdf';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();

                    setTimeout(() => URL.revokeObjectURL(url), 10000);
                })
                .catch(() => {
                    this.spin = false;
                    this.$toasted.show('Error al generar PDF', toast_options);
                });
        },

        getxls() {
            this.spin = true;

            axios.post(urldomine + 'api/reports/export', { filters: this.filters }, {
                responseType: 'blob'
            }).then(response => {
                this.spin = false;

                const contentType = response.headers['content-type'];
                if (!contentType.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                    const reader = new FileReader();
                    reader.onload = () => {
                        this.$toasted.show(reader.result || 'Error desconocido', toast_options);
                    };
                    reader.readAsText(response.data);
                    return;
                }

                const blob = new Blob([response.data], { type: contentType });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'reporte.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();

                setTimeout(() => URL.revokeObjectURL(url), 10000);
            }).catch(() => {
                this.spin = false;
                this.$toasted.show('Error al generar XLS', toast_options);
            });
        },

        cleardiv() {
            this.filters.division = 0
        },
        clearrol() {
            this.filters.rol = 0
        },
        clearper() {
            this.filters.person = 0
        },
        cleardate() {
            this.filters.dstar = 0;
            this.filters.dend = 0
        },
        getlist(pFil, pOrder, pPager) {
            if (pFil !== undefined) { this.filters = pFil, this.pager.page = 1 }

            if (pOrder !== undefined) { this.orders = pOrder }

            if (pPager !== undefined) { this.pager = pPager }

            this.spin = true;

            axios({
                method: 'get',

                url: urldomine + 'api/reports/list',

                params: { start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders }

            }).then(response => {

                this.spin = false;

                this.lists = response.data.list;

                this.divisions = response.data.divisions;

                this.rols = response.data.rols;

                this.persons = response.data.persons;

                this.totalpage = Math.ceil(response.data.total / this.pager.recordpage);

                this.totales_tokens = response.data.totales_tokens || {};

                this.tokens_finalizados = response.data.tokens_finalizados || [];

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);
            })
        }
    },
    computed: {
        groupedLists() {
            const grouped = {};
            this.lists.forEach(item => {
                const key = item.token || item.person_id;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(item);
            });
            return grouped;
        }
    }

});
