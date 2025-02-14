Vue.config.devtools = true;

Vue.use(Toasted);

new Vue({
    el: '#app',
    data () {
        return {
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
            listfield: [{name: 'Fecha', type: 'date', field: 'persons_checks.moment'}, {name: 'Motivo', type: 'select',  field: 'persons_checks.motive_id'}],
            fieldtype: 'text',
            filters: {
                division: 0,
                rol: 0,
                person: 0,
                dstar: moment().hour(8).minute(40),
                dend: moment().hour(8).add(9, 'hour').minute(10),
            },
            orders: {
                field: 'persons_checks.moment',
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
            this.getlist()
        },
        'filters.rol': function () {
            this.getlist()
        },
        'filters.person': function () {
            this.getlist()
        },
        'filters.dstar': function () {
            this.getlist()
        },
        'filters.dend': function () {
            this.getlist()
        },
    },
    mounted () {
        $('input[name="datetimes"]').daterangepicker({
            timePicker: true,
            opens: 'left',
            cancelClass: "btn-danger",
            startDate: moment().hour(8).minute(40),
            endDate: moment().hour(8).add(9, 'hour').minute(10),
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
        },(start, end, label) => {

           this.filters.dstar = start.format('YYYY-MM-DD H:mm');

           this.filters.dend =  end.format('YYYY-MM-DD H:mm');

        });

        this.formData = new FormData();

        this.getlist();

    },
    methods: {
        getpdf(){

            this.spin = true;

            axios.post(urldomine + 'api/reports/pdf', {filters: this.filters}).then(response => {

                this.spin = false;

                window.$('#iframe').attr('src', response.data);

                window.$('#pdf').modal('show')

            }).catch(er => {

                this.spin = false;

                this.$toasted.show(er.response.data, toast_options);
            })
        },
        cleardiv () {
           this.filters.division = 0
        },
        clearrol () {
            this.filters.rol = 0
        },
        clearper () {
            this.filters.person = 0
        },
        cleardate() {
            this.filters.dstar = 0;
            this.filters.dend = 0
        },
        getlist (pFil, pOrder, pPager) {
            if (pFil !== undefined) { this.filters = pFil }

            if (pOrder !== undefined) { this.orders = pOrder }

            if (pPager !== undefined) { this.pager = pPager }

            this.spin = true;

            axios({
                method: 'get',

                url: urldomine + 'api/reports/list',

                params: {start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders}

            }).then(response => {

                this.spin = false;

                this.lists = response.data.list;

                this.divisions = response.data.divisions;

                this.rols = response.data.rols;

                this.persons = response.data.persons;

                this.totalpage = Math.ceil(response.data.total / this.pager.recordpage)

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);
            })
        }
    }
});
