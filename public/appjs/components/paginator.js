Vue.component('paginator', {
    props: ['tpage', 'pager'],
    template:
        `<div>
      <ul v-if="tpage > 1" class="pagination">
        <li class="page-item" :class="{disabled: currentpage === 1}">
<a class="page-link" @click = "setpage(1)"> <span class="fa fa-angle-double-left " aria-hidden="true"></span></a></li>
        <li class="page-item" :class="{disabled: currentpage === 1}">
<a class="page-link" @click = "setpage(currentpage - 1)"><span class="fa fa-chevron-left " aria-hidden="true"></span></a></li>
        <li class="page-item" v-for="pagex in rango(tpage, currentpage)" :key="pagex" :class="{active: currentpage == pagex}"><a class="page-link" @click = "setpage(pagex)"> {{pagex}}</a></li>
        <li class="page-item" :class="{disabled: currentpage === tpage}">
<a class="page-link" @click = "setpage(currentpage + 1)"><span class="fa fa-angle-right" aria-hidden="true"></span></a></li>
        <li class="page-item" :class="{disabled: currentpage === tpage}"><a class="page-link" @click = "setpage(tpage)"><span class="fa fa-angle-double-right " aria-hidden="true"></span></a></li>
      </ul>
    </div>`,
    methods: {
        setpage(page) {
            if (page < 1 || page > this.tpage || page === this.currentpage) return;
            this.currentpage = page;
            this.pager.page = page;
            this.$emit('getresult', undefined, undefined, this.pager);
        }
    },
    watch: {
        tpage: function () {
            this.currentpage = 1
        }
    },
    data() {
        return {
            pagex: '',
            currentpage: 1,
            recordpage: this.pager.recordpage,
            rango: rangoutil
        }
    }
});
