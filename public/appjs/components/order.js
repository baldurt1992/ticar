Vue.component('order', {
    props: ['options', 'labels', 'field'],
    template:
        `<label :class="uses()"  @click="setorder()">{{labels}} <i :class="getorder()"></i></label>`,
    data () {
        return {
            O: this.options
        }
    },
    methods: {
        setorder () {

            this.O.field = this.field;

            this.O.type = this.O.type === 'asc' ? 'desc' : 'asc';

            this.$emit('getfilter', undefined, this.filters, undefined)
        },
        getorder() {

            if (this.options.field === this.field) {

                return  this.options.type === 'asc' ? 'fa fa-sort-up' : 'fa fa-sort-down';

            } else {

                return 'fa fa-sort';
            }
        },
        uses() {

           return  this.options.field === this.field ? 'mouse orderuse' : 'mouse'

        }
    }
});