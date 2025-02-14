@extends('layouts.app')

@section('content')
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
            <h3 class="page-title">Listado de ocupaciones</h3>
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
                                <input type="text" class="form-control" v-model="item.rol">
                                <span v-if="erros.rol" class="font-13 text-error">@{{ erros.name[0] }}</span>
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
                                <th scope="col" class="border-0"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="entity in lists" class="mouse">
                                <td>@{{ entity.rol }}</td>
                                <td>
                                    <button class="btn btn-success btn-sm" @click="edit(entity)"><i class="fa fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm" @click="showdelete(entity)"><i class="fa fa-eraser"></i></button>
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
    <script src="{{asset('appjs/rols.js')}}"></script>
    <script src="{{asset('appjs/components/paginator.js')}}"></script>
    <script src="{{asset('appjs/components/order.js')}}"></script>
@endsection
