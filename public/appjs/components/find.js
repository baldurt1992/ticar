Vue.component('find', {
    props: ['filters', 'filter', 'holder'],
    template:
        `<div class="find">
    <div class="input-group">
    <input type="text" class="form-control input-sm" :placeholder="holder" v-model="find" @keyup="updates()">
    <div class="input-group-btn">
    <button class="btn btn-default btn-sm"><i class="fa fa-search"></i></button>`,
    data () {
        return {
            find: this.filters[this.filter]
        }
    },
    methods: {
        updates () {
            this.filters[this.filter] = this.find;
            this.$emit('getfilter', this.filters, undefined, undefined)
        }
    }
});