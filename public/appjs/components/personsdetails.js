Vue.component('persons-d', {
    props: ['persons_id'],
    template:
        `<div  class="row">
        <div class="col-lg-12">
            <div class="card card-small mb-4">
                <div class="card-header border-bottom">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12 m-b-5">
                        </div>
                      </div>
                    </div>
                </div>
                <div class="card-body p-0 pb-3">
                    <table class="table mb-0 table-hover">
                        <thead class="bg-light">
                        <tr>
                            <th scope="col" class="border-0">Código</th>
                            <th scope="col" class="border-0">Nombre</th>
                            <th scope="col" class="border-0">Dirección</th>
                            <th scope="col" class="border-0">Telefono</th>
                            <th scope="col" class="border-0">Email</th>
                            <th scope="col" class="border-0">Estado</th>
                            <th scope="col" class="border-0"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="entity in lists" class="mouse">
                            <td>{{ entity.token }}</td>
                            <td>{{ entity.names }}</td>
                            <td>{{ entity.address }}</td>
                            <td>{{ entity.phone }}</td>
                            <td>{{ entity.email }}</td>
                            <td>{{ entity.status.status }}</td>
                            <td>
                            
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>`,
    data () {
        return {
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
            listfield: [{name: 'Nombre', type: 'text', field: 'persons.name'}, {name: 'Dirección', type: 'text',  field: 'persons.address'}],
            fieldtype: 'text',
            filters: {
                descrip: 'Nombre',
                field: 'persons.names',
                value: ''
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
            datas:[]
        }
    },
    watch: {
        'filters.value': function () {
            this.getlist()
        }
    },
    mounted () {

        this.getlist();

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

                url: urldomine + 'api/persons/data',

                params: {start: this.pager.page - 1, take: this.pager.recordpage, filters: this.filters, orders: this.orders}

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
        getdata (it) {

            this.item = JSON.parse(JSON.stringify(it));

            this.onview('details')
        },
        delitem () {

            this.spin = true;

            axios({

                method: 'delete',

                url: urldomine + 'api/persons/data' + this.item.id

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
        close () {

            this.add();

            this.onview('list')

        },
        onview (pro) {

            for (let property in this.views) {

                this.views[property] = property === pro
            }
        }
    }
});
