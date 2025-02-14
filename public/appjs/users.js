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
            payments_types: [],
            payments_deadlines: [],
            comercials: [],
            views: {
                list: true,
                new: false,
                import: false,
            },
            unlookview: false,
            importview: false,
            item: {
                id: 0,
                name: '',
                password: '',
                email: '',
                status_id: false,

            },
            listfield: [{name: 'Nombre', type: 'text', field: 'users.name'}],
            fieldtype: 'text',
            filters: {
                descrip: 'Nombre',
                field: 'users.name',
                value: ''
            },
            orders: {
                field: 'users.name',
                type: 'asc'
            },
            pager: {
                page: 1,
                recordpage: 9
            },
            totalpage: 0,
            center: {
                lat: 0,
                lng: 0
            }
        }
    },
    watch: {
        'filters.value': function () {
            this.getlist()
        }
    },
    mounted () {

        this.getlist();

        this.formData = new FormData();

    },
    methods: {
        valid: validd,
        setfield (f){

            this.filters.value = '';

            this.filters.descrip = f.name;

            this.filters.field = f.field;

            if (f.type === 'select') this.filters.options = f.options;

            this.fieldtype = f.type

        },
        getlist (pFil, pOrder, pPager) {
            if (pFil !== undefined) { this.filters = pFil }

            if (pOrder !== undefined) { this.orders = pOrder }

            if (pPager !== undefined) { this.pager = pPager }

            this.spin = true;

            axios({
                method: 'get',

                url: urldomine + 'api/users/list',

                params: {start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders}

            }).then(response => {

                this.spin = false;

                this.lists = response.data.list;

                this.totalpage = Math.ceil(response.data.total / this.pager.recordpage)

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);
            })
        },
        add () {
            this.title = 'Añadir usuario';

            this.item.name = '';

            this.item.email = '';

            this.item.password = '';

            this.act = 'post';

            this.onview('new')

        },
        edit (it) {

            this.item = JSON.parse(JSON.stringify(it));

            this.act = 'put';

            this.title = 'Actualizar usuario: ' + this.item.name;

            this.onview('new')

        },
        save () {

            this.spin = true;

            delete this.item['status'];

            let data = {

                'user': this.item,
            };

            axios({

                method: this.act,

                url: urldomine + 'api/users' + (this.act === 'post' ? '' : '/' + this.item.id),

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
        delitem () {

            this.spin = true;

            axios({

                method: 'delete',

                url: urldomine + 'api/users/' + this.item.id

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

            this.delobj = it.name;

            $('#modaldelete').modal('show')

        },
        close () {

            this.add();

            this.onview('list')

        },

        pass () {

           let name = this.item.name !== '';

           let password = this.act === 'put' ? true : this.item.password !== '';

           let email = /^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/i.test(this.item.email);

           return name && password && email
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
        onview (pro) {

            for (let property in this.views) {

                this.views[property] = property === pro
            }
        }
    }
});
