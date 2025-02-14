@extends('layouts.app')

@section('content')
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-12 text-center text-sm-left mb-0">
            <h4 class="page-title">Listado de tabajadores de <b><span>@{{ division.names }}</span></b></h4>
            <input type="text" id="division" value="{{$id}}" hidden>
        </div>
    </div>

        <div v-if="views.list" class="row">
            <div class="col-lg-12">
                <div class="card card-small mb-4">
                    <div class="card-header border-bottom">
                        <div class="row">
                            <div class="col-lg-1 col-sm-1 text-center text-sm-left mb-0">
                                <button class="btn btn-link btn-sm" @click="back()"><i class="fa fa-chevron-circle-left "></i> Regresar</button>
                            </div>
                            <div class="col-lg-7 col-sm-1 text-center text-sm-left mb-0">
                                <paginator :tpage="totalpage" :pager="pager" v-on:getresult="getlist"></paginator>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12"> @component('components.find')@endcomponent</div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-3">
                        <table class="table mb-0 table-hover">
                            <thead class="bg-light">
                            <tr>
                                <th scope="col" class="border-0">Código</th>
                                <th scope="col" class="border-0">Nombre</th>
                                <th scope="col" class="border-0">Dirección</th>
                                <th scope="col" class="border-0">Telefono</th>
                                <th scope="col" class="border-0">Email</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="entity in lists" class="mouse">
                                <td>@{{ entity.token }}</td>
                                <td>@{{ entity.names }}</td>
                                <td>@{{ entity.address }}</td>
                                <td>@{{ entity.phone }}</td>
                                <td>@{{ entity.email }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-lg-1 col-sm-1 text-center text-sm-left mb-0">
                                <button class="btn btn-link btn-sm" @click="back()"><i class="fa fa-chevron-circle-left "></i> Regresar</button>
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
    <script src="{{asset('appjs/personsdiv.js')}}"></script>
@endsection
