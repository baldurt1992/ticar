@extends('layouts.app')

@section('content')
    <div v-if="views.list" class="row" style="margin-top: 10px">
        <div class="col-lg-12">
            <div class="card card-small mb-4">
                <div class="card-header border-bottom">
                    <div class="row align-items-center mb-3">
                        <div class="col-12 col-md-auto mb-2 mb-md-0">
                            <button class="btn btn-success mr-1" @click="getpdf()"><i class="fa fa-file-pdf"></i></button>
                            <button class="btn btn-success" @click="getxls()"><i class="fa fa-file-excel"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 pb-3">
                    <div class="p-3">
                        <div class="form-row">
                            <div class="form-group col-12 col-md-3">
                                <label>Sucursal</label>
                                <div class="input-group">
                                    <select class="form-control custom-select" v-model="filters.division">
                                        <option v-for="div in divisions" :value="div . id">@{{ div.names }}</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" @click="cleardiv()"><i
                                                class="material-icons">home</i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-3">
                                <label>Rol</label>
                                <div class="input-group">
                                    <select class="form-control custom-select" v-model="filters.rol">
                                        <option v-for="rl in rols" :value="rl . id">@{{ rl.rol }}</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" @click="clearrol()"><i
                                                class="material-icons">subject</i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-3">
                                <label>Persona</label>
                                <div class="input-group">
                                    <select class="form-control custom-select" v-model="filters.person">
                                        <option v-for="per in persons" :value="per . id">@{{ per.names }}</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary" @click="clearper()"><i
                                                class="material-icons">supervised_user_circle</i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-3">
                                <label>Fechas</label>
                                <div class="input-group">
                                    <input type="text" name="datetimes" id="datetimes" class="form-control"
                                        value="{{ $start_date }} - {{ $end_date }}" />
                                    <div class="input-group-append">
                                        <button class="btn btn-secondary"><i class="fa fa-calendar-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover w-100 mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col" class="border-0">Sucursal</th>
                                    <th scope="col" class="border-0">Rol</th>
                                    <th scope="col" class="border-0">Codigo</th>
                                    <th scope="col" class="border-0">Nombre</th>
                                    <th scope="col" class="border-0">Entrada</th>
                                    <th scope="col" class="border-0">Salida</th>
                                    <th scope="col" class="border-0">Horas</th>
                                    <th scope="col" class="border-0"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(group, token) in groupedLists">
                                    <tr v-if="tokens_finalizados.map(String).includes(String(token))"
                                        class="font-weight-bold" style="background-color: #d6d6d6;">
                                        <td colspan="6" class="text-right">
                                            Total horas de <strong>@{{ group[0].names }}</strong>:
                                        </td>
                                        <td>@{{ totales_tokens[token] || '00:00' }}</td>
                                        <td></td>
                                    </tr>
                                    <tr v-for="item in group" :key="item . id">
                                        <td>@{{ item.div }}</td>
                                        <td>@{{ item.rol }}</td>
                                        <td>@{{ item.token }}</td>
                                        <td>@{{ item.names }}</td>
                                        <td>@{{ formatFecha(item.moment_enter) }}</td>
                                        <td>@{{ formatFecha(item.moment_exit) }}</td>
                                        <td>@{{ item.hours }}</td>
                                        <td></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-lg-1 col-sm-1 text-center text-sm-left mb-0">
                        </div>
                        <div class="col-lg-11 col-sm-1 text-center text-sm-left mb-0">
                            <paginator :tpage="totalpage" :pager="pager" v-on:getresult="getlist"></paginator>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="pdf" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h5 class="modal-title" style="color: black" id="exampleModalLabel">Visor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe id="iframe" src="" frameborder="0" width="100%" height="450px"></iframe>
                </div>
                <div class="modal-footer">
                    <a href="#" data-dismiss="modal" class="btn btn-default  btn-sm">Cerrar</a>
                </div>
            </div>
        </div>
    </div>

    @component('components.eliminar')@endcomponent
    @component('components.spiner')@endcomponent
@endsection
@section('script')
    @parent
    <script src="{{asset('appjs/components/paginator.js')}}"></script>
    <script src="{{asset('appjs/components/personsdetails.js')}}"></script>
    <script src="{{asset('appjs/components/order.js')}}"></script>
    <script src="{{asset('appjs/report.js')}}"></script>
@endsection