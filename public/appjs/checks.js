Vue.config.devtools = true;

Vue.use(Toasted);

new Vue({
    el: '#app',
    data() {
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
            item: {
                id: 0,
                person_id: '',
                moment: '',
                division_id: '',
                motive_id: '',
                email: '',
                note: '',
                url_screen: ''
            },
            listfield: [{ name: 'Fecha', type: 'date', field: 'persons_checks.moment' }, { name: 'Motivo', type: 'select', field: 'persons_checks.motive_id' }],
            fieldtype: 'text',
            filters: {
                descrip: 'Fecha',
                field: 'persons_checks.moment',
                value: '',
                person_id: 0
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
            motives: [],
            motive: 0,
            moment: 0,
            img: '',
            person: {}
        }
    },
    watch: {
        'motive': function () {
            this.filters.field = 'persons_checks.motive_id';
            this.filters.value = this.motive;
            this.getlist()
        },
        'moment': function () {
            this.filters.field = 'persons_checks.moment';
            this.filters.value = this.moment;
            this.getlist()
        }
    },
    mounted() {

        this.formData = new FormData();

        this.filters.person_id = $('#person').val();

        this.getlist();


    },
    methods: {
        formatFecha(fecha) {
            return fecha ? moment(fecha).format('DD/MM/YY HH:mm') : '-';
        },
        imgFull(x) {
            this.img = x;
            $('#img').modal('show');
        },
        setfield(f) {

            this.filters.value = '';

            this.filters.descrip = f.name;

            this.filters.field = f.field;

            if (f.type === 'select') this.filters.options = f.options;

            this.fieldtype = f.type

        },
        getlist(pFil, pOrder, pPager) {
            if (pFil !== undefined) { this.filters = pFil }

            if (pOrder !== undefined) { this.orders = pOrder }

            if (pPager !== undefined) { this.pager = pPager }

            this.spin = true;

            axios({
                method: 'get',

                url: urldomine + 'api/checks/list',

                params: { start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders }

            }).then(response => {

                this.spin = false;

                this.lists = response.data.list;

                this.motives = response.data.motives;

                this.person = response.data.person;

                this.totalpage = Math.ceil(response.data.total / this.pager.recordpage)

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);
            })
        },
        back(it) {
            location.href = urldomine + 'persons'
        },
        delitem() {

            this.spin = true;

            axios({

                method: 'delete',

                url: urldomine + 'api/checks/' + this.item.id

            }).then(response => {

                this.spin = false;

                $('#modaldelete').modal('hide');

                this.$toasted.show(response.data, toast_options);

                this.getlist();

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);

            })

        },
        showdelete(it) {

            this.item = it;

            this.delobj = it.moment;

            $('#modaldelete').modal('show')

        },
        close() {

            this.add();

            this.onview('list')

        },

        pass() {

            let name = this.item.name !== '';

            let token = this.item.token !== '';

            let address = this.item.address !== '';

            let email = /^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/i.test(this.item.email);

            return name && address && token && email
        },
        getfile(e) {

            let files = e.target.files || e.dataTransfer.files;

            if (!files.length) {

                this.file = null

            } else {

                this.file = files[0];

                this.formData.append('importfile', this.file)
            }
        },
        onview(pro) {

            for (let property in this.views) {

                this.views[property] = property === pro
            }
        }
    }
});
