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
                names: '',
                token: '',
                address: '',
                phone: '',
                email: '',
                note: '',
                created_at: '',
                status_id: false,
            },
            persons: {
                rols: [],
                divisions: []
            },
            listfield: [{ name: 'Nombre', type: 'text', field: 'persons.name' }, { name: 'Dirección', type: 'text', field: 'persons.address' }],
            fieldtype: 'text',
            filters: {
                descrip: 'Nombre',
                field: 'persons.names',
                value: '',
            },
            orders: {
                field: 'persons.names',
                type: 'asc'
            },
            pager: {
                page: 1,
                recordpage: 9
            },
            totalpage: 0,
            rols: [],
            divisions: [],
            datas: []

        }
    },
    watch: {
        'filters.value': function () {
            this.getlist()
        }
    },
    mounted() {

        this.getlist();

        this.formData = new FormData();

    },
    methods: {
        valid: validd,
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

                url: urldomine + 'api/persons/list',

                params: { start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders }

            }).then(response => {

                this.spin = false;

                this.lists = response.data.list;

                this.rols = response.data.rols;

                this.divisions = response.data.divisions;

                this.totalpage = Math.ceil(response.data.total / this.pager.recordpage)

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);
            })
        },
        getdata(it) {
            location.href = urldomine + 'checks/' + it.id
        },
        add() {
            this.title = 'Añadir trabajador';

            this.item.names = '';

            this.item.address = '';

            this.item.phone = '';

            this.item.note = '';

            this.item.email = '';

            this.item.created_at = '';

            this.persons.rols = [];

            this.persons.divisions = [];

            this.act = 'post';

            this.onview('new')

        },
        edit(it) {

            this.item = JSON.parse(JSON.stringify(it));

            this.item.created_at = fixdate(new Date(this.item.created_at));

            this.persons.rols = this.item.rols.map(i => { return i.rol_id });

            this.persons.divisions = this.item.divisions.map(i => { return i.division_id });

            this.act = 'put';

            this.title = 'Actualizar trabajador: ' + this.item.names;

            this.onview('new')

        },
        save() {

            this.spin = true;

            delete this.item.status;

            delete this.item.divisions;

            delete this.item.rols;

            let data = {

                'person': this.item,

                'data': this.persons
            };

            axios({

                method: this.act,

                url: urldomine + 'api/persons' + (this.act === 'post' ? '' : '/' + this.item.id),

                data: data

            }).then(response => {

                this.spin = false;

                this.$toasted.show(response.data, toast_options);

                this.getlist();

                this.onview('list')

            }).catch(e => {

                this.spin = false;

                this.erros = e.response.data.errors

            })

        },
        delitem() {

            this.spin = true;

            axios({

                method: 'delete',

                url: urldomine + 'api/persons/' + this.item.id

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

            this.delobj = it.names;

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
