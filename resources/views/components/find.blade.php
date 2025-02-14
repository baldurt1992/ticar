<div class="input-group">
    <div v-if="fieldtype === 'select'" >
        <select v-model="filters.value" class="form-control">
            <option v-for="op in filters.options" :value="op.id">@{{ op.name }}</option>
        </select>
    </div>
    <input v-if="fieldtype !== 'select'" :type="fieldtype"  class="form-control" placeholder="buscar.." v-model="filters.value">
    <div class="input-group-append">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" style="overflow: hidden; position: relative;">@{{ filters.descrip }} <span class="caret"></span></button>
        <ul class="dropdown-menu">
            <li class="dropdown-item" v-for="field in listfield"><a href="javascript:void(0)" @click="setfield(field)">@{{ field.name }}</a></li>
        </ul>
    </div>
</div>
