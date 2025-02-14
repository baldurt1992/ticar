@extends('layouts.app')

@section('content')
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
            <h3 class="page-title">Listado de sucursales</h3>
        </div>
    </div>

    <div v-if="views.new" class="row" v-cloak>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <label>@{{title}}</label>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="form-group">
                                <label >Nombre *</label>
                                <input type="text" class="form-control" v-model="item.names">
                                <span v-if="erros.name" class="font-13 text-error">@{{ erros.name[0] }}</span>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="form-group m-t-10">
                                <label >Dirección *</label> (calle , número interior o exterior, ciudad, municipio, estado)
                                <input type="text" class="form-control" v-model="item.address">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group m-t-10">
                                <label >Telefono</label>
                                <input type="tel" class="form-control" v-model="item.phone" @keydown="valid($event)">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label >email *</label>
                                <div class="input-group">
                                    <input type="email" id="example-input2-group2" name="example-input2-group2" class="form-control" v-model="item.email">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Hora Inicio *</label>
                                <div class="input-group">
                                    <input type="time" id="example-input2-group2" name="example-input2-group2" class="form-control" v-model="item.workbegin">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Hora final *</label>
                                <div class="input-group">
                                    <input type="time" id="example-input2-group2" name="example-input2-group2" class="form-control" v-model="item.workend">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-4">
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox" class="custom-control-input" v-model="item.status_id" id="formsCheckboxDefault">
                                <label class="custom-control-label" for="formsCheckboxDefault">Activo</label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button v-if="pass()" class="btn btn-success btn-sm" @click="save()">Guardar</button>
                    <button class="btn btn-default btn-sm" @click="close()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

        <div v-if="views.list" class="row">
            <div class="col-lg-12">
                <div class="card card-small mb-4">
                    <div class="card-header border-bottom">
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12 m-b-5">
                                    <button class="btn btn-primary btn-sm" @click="add()">Nuevo</button>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12"> @component('components.find')@endcomponent</div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-3">
                        <table class="table mb-0 table-hover">
                            <thead class="bg-light">
                            <tr>
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
                                <td>@{{ entity.names }}</td>
                                <td>@{{ entity.address }}</td>
                                <td>@{{ entity.phone }}</td>
                                <td>@{{ entity.email }}</td>
                                <td>@{{ entity.status.status }}</td>
                                <td>
                                    <button class="btn btn-success btn-sm" @click="edit(entity)"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm" @click="showdelete(entity)"><i class="fa fa-eraser"></i></button>
                                    <button class="btn btn-info btn-sm" @click="getdata(entity)"><i class="fa fa-id-card"></i></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

@component('components.eliminar')@endcomponent
@component('components.spiner')@endcomponent
@endsection
@section('script')
    @parent
    <script src="{{asset('appjs/division.js')}}"></script>
    <script src="{{asset('appjs/components/paginator.js')}}"></script>
    <script src="{{asset('appjs/components/order.js')}}"></script>
@endsection
