{{-- vue-mode --}}
@extends('layouts.app')

@section('content')
    <div v-if="views.list" class="row" style="margin-top: 10px">
        <div class="col-lg-12">
            <div class="card card-small mb-4">
                <div class="card-header border-bottom">
                    <div class="row align-items-center mb-3 justify-content-between">
                        <div class="col-12 col-md-auto mb-2 mb-md-0">
                            <button class="btn btn-success mr-1" @click="getpdf()"><i class="fa fa-file-pdf"></i></button>
                            <button class="btn btn-success" @click="getxls()"><i class="fa fa-file-excel"></i></button>
                        </div>
                        <div class="col-12 col-md-auto mb-2 mb-md-0 ">
                            <button type="button" data-toggle="modal" data-target="#CustomReport"
                                class="btn btn-dark font-weight-bold">Reporte
                                personalizado</button>
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
                                    <tr v-for="(item, i) in group" :key="token + '-' + i">
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

    <div class="modal fade" id="CustomReport" tabindex="-1" aria-labelledby="CustomReport" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 40vw; min-height:auto; height:auto;">
            <div class="modal-content" style="height:100%; width: auto;">
                <div class="modal-header">
                    <h5 class="modal-title text-light" id="CustomReport">Reporte personalizado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="d-flex flex-column col-md-6">
                            <h4 class="font-weight-bold text-dark">Campos del reporte</h4>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="division">
                                <label class="form-check-label" for="division">Sucursal</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="role">
                                <label class="form-check-label" for="role">Rol</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="token">
                                <label class="form-check-label" for="token">Código</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="name">
                                <label class="form-check-label" for="name">Nombre</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="moment_enter">
                                <label class="form-check-label" for="moment_enter">Entrada</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="moment_exit">
                                <label class="form-check-label" for="moment_exit">Salida</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="hours">
                                <label class="form-check-label" for="hours">Horas</label>
                            </div>
                            <div class="mt-3">
                                <h4 class=" text-dark font-weight-bold">Formato</h4>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="pdf">
                                    <label class="form-check-label" for="pdf">PDF</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="excel">
                                    <label class="form-check-label" for="excel">Excel</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" d-flex flex-column">
                                <h4 class="text-dark font-weight-bold mb-2">Selecciona el día</h4>
                                <div id="calendar" class="mb-4" style="min-height: 250px;"></div>

                                <h4 class="text-dark font-weight-bold mb-2">Selecciona periodicidad</h4>
                                <select class="form-control" v-model="custom_report.schedule">
                                    <option value="daily">Todos los días</option>
                                    <option value="weekly">Cada semana</option>
                                    <option value="monthly">Cada mes</option>
                                </select>

                                <label class="mt-3 font-weight-bold text-dark">Hora de envío</label>
                                <input type="time" class="form-control" v-model="custom_report.custom_time">
                                <div class="form-group mt-3">
                                    <label for="userSearch">Seleccionar usuarios para enviar</label>

                                    <div class="dropdown">
                                        <input type="text" class="form-control dropdown-toggle" data-toggle="dropdown"
                                            v-model="userSearch" placeholder="Buscar por nombre o correo"
                                            autocomplete="off" />

                                        <div class="dropdown-menu" style="width: 100%; max-height: 200px; overflow-y: auto;"
                                            v-if="filteredUsers.length">
                                            <button class="dropdown-item d-flex justify-content-between align-items-center"
                                                v-for="user in filteredUsers" :key="user.id"
                                                @click.prevent="toggleUser(user)">
                                                <span>@{{ user.names }} - @{{ user.email }}</span>
                                                <i class="fa"
                                                    :class="isSelected(user) ? 'fa-check-square text-success' : 'fa-square-o text-muted'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <label class="mt-3 font-weight-bold text-dark">Correos adicionales</label>
                                    <input type="text" class="form-control" v-model="emailInput"
                                        placeholder="correo@dominio.com, otro@dominio.com">
                                    <div class="mt-2">
                                        <span v-for="user in selectedUsers" :key="user.id" class="badge badge-primary mr-1">
                                            @{{ user.names }}
                                            <i class="fa fa-times ml-1" @click="removeUser(user)"
                                                style="cursor: pointer;"></i>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary " data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" @click="createCustomReport">Crear reporte</button>
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