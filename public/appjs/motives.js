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
                import: false,
            },
            unlookview: false,
            importview: false,
            item: {
                id: 0,
                motive: '',
                discount_time: 0
            },
            listfield: [
                { name: 'Nombre', type: 'text', field: 'motives.motive' },
                { name: 'Descuento de tiempo', type: 'checkbox', field: 'motives.discount_time' } // Nuevo campo checkbox
            ],
            fieldtype: 'text',
            filters: {
                descrip: 'Nombre',
                field: 'motives.motive',
                value: ''
            },
            orders: {
                field: 'motives.motive',
                type: 'asc'
            },
            pager: {
                page: 1,
                recordpage: 9
            },
            totalpage: 0,
            rols: []
        }
    },
    watch: {
        'filters.value': function () {
            this.getlist()
        }
    },
    mounted() {

        this.getlist();

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

                url: urldomine + 'api/motives/list',

                params: { start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders }

            }).then(response => {

                this.spin = false;

                this.lists = response.data.list;

                this.totalpage = Math.ceil(response.data.total / this.pager.recordpage)

            }).catch(e => {

                this.spin = false;

                this.$toasted.show(e.response.data, toast_options);
            })
        },
        add() {
            this.title = 'Añadir motivo';

            this.item.motive = '';

            this.act = 'post';

            this.onview('new')

        },
        edit(it) {

            this.item = JSON.parse(JSON.stringify(it));

            this.act = 'put';

            this.title = 'Actualizar motivo: ' + this.item.motive;

            this.onview('new')

        },
        save() {

            this.spin = true;

            delete this.item['status'];

            let data = {

                'motive': this.item,
            };

            axios({

                method: this.act,

                url: urldomine + 'api/motives' + (this.act === 'post' ? '' : '/' + this.item.id),

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

                url: urldomine + 'api/motives/' + this.item.id

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

            this.delobj = it.motive;

            $('#modaldelete').modal('show')

        },
        close() {

            this.add();

            this.onview('list')

        },
        pass() {
            return this.item.motive !== '';
        },
        onview(pro) {

            for (let property in this.views) {

                this.views[property] = property === pro
            }
        }
    }
});
