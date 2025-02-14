@extends('layouts.app')

@section('content')
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
            <h3 class="page-title">Generales de la empresa</h3>
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

                    </div>

                </div>
                <div class="card-footer">
                    <button v-if="pass()" class="btn btn-success btn-sm" @click="save()">Guardar</button>
                </div>
            </div>
        </div>
    </div>



@component('components.eliminar')@endcomponent
@component('components.spiner')@endcomponent
@endsection
@section('script')
    @parent
    <script src="{{asset('appjs/company.js')}}"></script>
    <script src="{{asset('appjs/components/paginator.js')}}"></script>
    <script src="{{asset('appjs/components/order.js')}}"></script>
@endsection
