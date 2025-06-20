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
                    <div class="position-relative" style="height: 30px;">
                        <span v-if="errors.columns" class="text-danger small error-span">Selecciona al menos un
                            campo.</span>
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
                        <div class="position-relative" style="height: 30px;">
                            <span v-if="errors.columns" class="text-danger small error-span">Selecciona al menos un
                                formato.</span>
                        </div>
                    </div>
                    <div class=" d-flex align-items-start justify-content-start">
                        <div class="w-100 d-flex">
                            <button style="max-width: 200px; height: 50px;" class="btn btn-success btn-block"
                                @click="createCustomReport">
                                <i class="fa fa-save mr-1"></i> Guardar Reporte
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="d-flex flex-column">
                        <h4 class=" font-weight-bold mb-2">Selecciona el día</h4>
                        <div id="calendar" class="mb-4" style="min-height: 250px;"></div>
                        <div class="position-relative" style="height: 30px;">
                            <span v-if="errors.columns" class="text-danger small error-span">Selecciona una fecha.</span>
                        </div>

                        <h4 class=" font-weight-bold mb-2">Selecciona periodicidad</h4>
                        <select class="form-control" v-model="custom_report.schedule">
                            <option value="daily">Todos los días</option>
                            <option value="weekly">Cada semana</option>
                            <option value="monthly">Cada mes</option>
                        </select>

                        <label class="mt-3 font-weight-bold ">Hora de envío</label>
                        <input type="time" class="form-control" v-model="custom_report.custom_time">
                        <div class="position-relative" style="height: 30px;">
                            <span v-if="errors.columns" class="text-danger small error-span">Selecciona una hora de
                                envío.</span>
                        </div>

                        <label class="font-weight-bold">Seleccionar usuarios</label>

                        <div class="form-group position-relative">
                            <input id="user-search" type="text" class="form-control"
                                placeholder="Buscar por nombre o correo" v-model="userSearch" @focus="dropdownOpen = true"
                                @keydown.down.prevent="highlight++" @keydown.up.prevent="highlight--"
                                @keydown.enter.prevent="selectHighlightedUser" />


                            <div class="dropdown-menu show" v-show="dropdownOpen && filteredUsers.length"
                                style="position: absolute; top: 100%; width: 100%; max-height: 200px; overflow-y: auto; z-index: 1000;"
                                @mousedown.prevent>
                                <button class="dropdown-item d-flex justify-content-between align-items-center"
                                    v-for="(user, index) in filteredUsers" :key="user.id"
                                    :class="{ 'active': highlight === index }" @mousedown.prevent="selectUser(user)">
                                    <span>@{{ user.name }} - @{{ user.email }}</span>
                                    <i class="fa"
                                        :class="isSelected(user) ? 'fa-check-square text-success' : 'fa-square-o text-muted'"></i>
                                </button>
                            </div>

                            <div class="mt-2">
                                <span v-for="user in selectedUsers" :key="'user-'+user.id" class="badge badge-primary mr-1">
                                    @{{ user.name }}
                                    <i class="fa fa-times ml-1" @click="removeUser(user)" style="cursor: pointer;"></i>
                                </span>
                            </div>

                        </div>
                        <div class="position-relative" style="height: 30px;">
                            <span v-if="errors.columns" class="text-danger small error-span">Agrega al menos un
                                destinatario.</span>
                        </div>

                        <label class="mt-3 font-weight-bold">Correos adicionales</label>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model="emailInput" placeholder="correo@dominio.com" />
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" @click="addManualEmail">Agregar</button>
                            </div>
                        </div>

                        <div class="mt-2">
                            <span v-for="(email, i) in manualEmails" :key="'email-'+i" class="badge badge-secondary mr-1">
                                @{{ email }}
                                <i class="fa fa-times ml-1" @click="removeManualEmail(i)" style="cursor: pointer;"></i>
                            </span>
                        </div>

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
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script src="{{ asset('appjs/report_custom.js') }}"></script>
    @endsection