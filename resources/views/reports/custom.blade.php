@extends('layouts.app')

@section('content')
    <div class="container-fluid pt-3">
        <div class="page-header row no-gutters py-4">
            <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
                <h3 class="page-title">Crear Reporte Personalizado</h3>
            </div>
        </div>

        <div class="card card-small shadow rounded p-4">
            <div class="row">
                <div class="col-md-4">
                    <h4 class="font-weight-bold ">Campos del reporte</h4>
                    <div class="form-check" v-for="field in fieldOptions" :key="field.id">
                        <input class="form-check-input" type="checkbox" :id="field.id">
                        <label class="form-check-label" :for="field.id">@{{ field.label }}</label>
                    </div>
                    <div style="min-height: 30px; height: 30px;">
                        <span v-if="errors.columns" class="text-danger small">Selecciona al menos un campo.</span>
                    </div>

                    <div class="mt-4">
                        <h4 class=" font-weight-bold">Formato</h4>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="pdf" id="pdf" v-model="formatSelection">
                            <label class="form-check-label" for="pdf">PDF</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="excel" id="excel"
                                v-model="formatSelection">
                            <label class="form-check-label" for="excel">CSV</label>
                        </div>
                        <div style="min-height: 30px; height: 30px;">
                            <span v-if="errors.format" class="text-danger small">Selecciona al menos un formato.</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="d-flex flex-column">
                        <h4 class=" font-weight-bold mb-2">Selecciona el día</h4>
                        <div id="calendar" class="mb-4" style="min-height: 250px;"></div>
                        <div style="min-height: 30px; height: 30px;">
                            <span v-if="errors.custom_day" class="text-danger small">Selecciona una fecha.</span>
                        </div>

                        <h4 class=" font-weight-bold mb-2">Selecciona periodicidad</h4>
                        <select class="form-control" v-model="custom_report.schedule">
                            <option value="daily">Todos los días</option>
                            <option value="weekly">Cada semana</option>
                            <option value="monthly">Cada mes</option>
                        </select>

                        <label class="mt-3 font-weight-bold ">Hora de envío</label>
                        <input type="time" class="form-control" v-model="custom_report.custom_time">
                        <div style="min-height: 30px; height: 30px;">
                            <span v-if="errors.custom_time" class="text-danger small">Selecciona una hora de envío.</span>
                        </div>
                        <div class="form-group mt-3 ">
                            <label for="userSearch" class="font-weight-bold">Seleccionar usuarios para enviar</label>
                            <div class="dropdown">
                                <input type="text" class="form-control dropdown-toggle" data-toggle="dropdown"
                                    v-model="userSearch" placeholder="Buscar por nombre o correo" autocomplete="off" />

                                <div class="dropdown-menu" style="width: 100%; max-height: 200px; overflow-y: auto;"
                                    v-if="filteredUsers.length">
                                    <button class="dropdown-item d-flex justify-content-between align-items-center"
                                        v-for="user in filteredUsers" :key="user.id" @click.prevent="toggleUser(user)">
                                        <span>@{{ user.names }} - @{{ user.email }}</span>
                                        <i class="fa"
                                            :class="isSelected(user) ? 'fa-check-square text-success' : 'fa-square-o text-muted'"></i>
                                    </button>
                                </div>
                                <div style="min-height: 30px; height: 30px;">
                                    <span v-if="errors.emails" class="text-danger small">Agrega al menos un
                                        destinatario.</span>
                                </div>
                            </div>

                            <label class="mt-3 font-weight-bold ">Correos adicionales</label>
                            <input type="text" class="form-control" v-model="emailInput"
                                placeholder="correo@dominio.com, otro@dominio.com">

                            <div class="mt-2">
                                <span v-for="user in selectedUsers" :key="user.id" class="badge badge-primary mr-1">
                                    @{{ user.names }}
                                    <i class="fa fa-times ml-1" @click="removeUser(user)" style="cursor: pointer;"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class=" d-flex align-items-end justify-content-start">
                <div class="w-100 d-flex">
                    <button style="max-width: 200px;" class="btn btn-success btn-block" @click="createCustomReport">
                        <i class="fa fa-save mr-1"></i> Guardar Reporte
                    </button>
                </div>
            </div>
        </div>

        @include('components.eliminar')
        @include('components.spiner')
    </div>
@endsection

@section('script')
    @parent
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ asset('appjs/report_custom.js') }}"></script>
@endsection