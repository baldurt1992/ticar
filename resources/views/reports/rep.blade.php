{{-- vue-mode --}}
@extends('layouts.app')

@section('content')
    <div v-if="views.list" class="row" style="margin-top: 10px">
        <div class="col-lg-12">
            <div class="card card-small mb-4">
                <div class="card-header border-bottom">
                    <div class="row align-items-center mb-3 ">
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
                            <thead class="bg-light" v-for="(group, token) in groupedLists">
                                <tr class="font-weight-bold" style="background-color: #d6d6d6;"
                                    v-if="group.ordenados.length">
                                    <td colspan="10">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <div style="font-size: 20px;">
                                                @{{ group.ordenados[0]?.names }}
                                            </div>
                                            <div class="text-left d-flex flex-column align-items-start">
                                                <div class="d-flex align-items-center" style="cursor: pointer;"
                                                    @click="priorizar_otros = false; pager.page = 1; getlist()"
                                                    :class="priorizar_otros === false ? 'text-primary font-weight-bold' : ''">
                                                    <i class="fa fa-filter text-secondary mr-1"></i>
                                                    Total horas <strong class="ml-1">@{{ totales_tokens[token] ||
                                                        '00:00:00' }}</strong>
                                                    <span style="display: inline-block; width: 22px;" class="ml-2">
                                                        <i v-show="priorizar_otros === false"
                                                            class="fa fa-times-circle text-muted" style="cursor: pointer;"
                                                            @click.stop="priorizar_otros = null; pager.page = 1; getlist()">
                                                        </i>
                                                    </span>
                                                </div>

                                                <div class="d-flex align-items-center mt-1" style="cursor: pointer;"
                                                    @click="priorizar_otros = true; pager.page = 1; getlist()"
                                                    :class="priorizar_otros === true ? 'text-danger font-weight-bold' : ''">
                                                    <i class="fa fa-filter text-danger mr-1"></i>
                                                    Total horas otros: <strong class="ml-1">@{{
                                                        totales_tokens_otros[String(token)] || '00:00:00'
                                                        }}</strong>
                                                    <span style="display: inline-block; width: 22px;" class="ml-2">
                                                        <i v-show="priorizar_otros === true"
                                                            class="fa fa-times-circle text-muted" style="cursor: pointer;"
                                                            @click.stop="priorizar_otros = null; pager.page = 1; getlist()">
                                                        </i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="col" class="border-0">Sucursal</th>
                                    <th scope="col" class="border-0">Rol</th>
                                    <th scope="col" class="border-0">Codigo</th>
                                    <th scope="col" class="border-0">Nombre</th>
                                    <th scope="col" class="border-0">Entrada</th>
                                    <th scope="col" class="border-0">Salida</th>
                                    <th scope="col" class="border-0">Horas</th>
                                    <th scope="col" class="border-0">Motivo</th>
                                    <th scope="col" class="border-0">Nota</th>
                                    <th scope="col" class="border-0"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(group, token) in groupedLists">
                                    <template v-if="group.ordenados.length">

                                        <tr v-for="item in group.ordenados"
                                            :key="`${item.token}-${item.id}-${item.moment_enter || ''}-${item.moment_exit || ''}`"
                                            :class="item.motive_id > 0 ? 'text-danger' : ''">
                                            <td>@{{ item.div }}</td>
                                            <td>@{{ item.rol }}</td>
                                            <td>@{{ item.token }}</td>
                                            <td>@{{ item.names }}</td>
                                            <td>@{{ formatFecha(item.moment_enter) }}</td>
                                            <td>@{{ formatFecha(item.moment_exit) }}</td>
                                            <td>@{{ item.hours }}</td>
                                            <td>@{{ getMotiveName(item.motive_id) }}</td>
                                            <td>@{{ item.note }}</td>
                                            <td></td>
                                        </tr>
                                    </template>
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
    @component('components.eliminar')@endcomponent
    @component('components.spiner')@endcomponent
@endsection
@section('script')
    @parent
    <script src="{{asset('appjs/components/paginator.js')}}"></script>
    <script src="{{asset('appjs/components/personsdetails.js')}}"></script>
    <script src="{{asset('appjs/components/order.js')}}"></script>
    <script src="{{asset('appjs/report.js')}}"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"
        rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

@endsection