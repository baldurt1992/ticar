@extends('layouts.app')

@section('content')
        <div v-if="views.list" class="row" style="margin-top: 10px">
            <div class="col-lg-12">
                <div class="card card-small mb-4">
                    <div class="card-header border-bottom">
                        <div class="row">
                            <div class="col-lg-2 col-sm-2 text-center text-sm-left mb-0">
                                <button class="btn btn-success" @click="getpdf()"><i class="fa fa-file-pdf "></i></button>
                                <button class="btn btn-success" onclick="event.preventDefault();
                                                     document.getElementById('xls-form').submit()">xls</button>
                                <form id="xls-form" action="{{ route('reportxls') }}" method="POST" style="display: none;">
                                    @csrf
                                    <input type="text" name="division" :value="filters.division" hidden>
                                    <input type="text" name="rol" :value="filters.rol" hidden>
                                    <input type="text" name="person" :value="filters.person" hidden>
                                    <input type="text" name="dstar" :value="filters.dstar" hidden>
                                    <input type="text" name="dend" :value="filters.dend" hidden>
                                </form>
                            </div>
                            <div class="col-lg-11 col-sm-1 text-center text-sm-left mb-0">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0 pb-3">
                        <table class="table mb-0 table-hover">
                            <thead class="bg-light">
                            <tr>
                                <th  class="border-0">
                                    <div class="input-group">
                                        <select class="form-control custom-select"  v-model="filters.division">
                                            <option v-for="div in divisions" :value="div.id">@{{ div.names }}</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-secondary" @click="cleardiv()"><i class="material-icons">home</i></button>
                                        </div>
                                    </div>
                                </th>
                                <th  class="border-0">
                                    <div class="input-group">
                                        <select class="form-control custom-select"  v-model="filters.rol">
                                            <option v-for="rl in rols" :value="rl.id">@{{ rl.rol }}</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-secondary"  @click="clearrol()"><i class="material-icons">subject</i></button>
                                        </div>
                                    </div>

                                </th>
                                <th class="border-0">
                                    <div class="input-group">
                                        <select class="form-control custom-select"  v-model="filters.person">
                                            <option v-for="per in persons" :value="per.id">@{{ per.names }}</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-secondary" @click="clearper()"><i class="material-icons">supervised_user_circle</i></button>
                                        </div>
                                    </div>
                                </th>
                                <th class="border-0">
                                    <div class="input-group">
                                        <input type="text" name="datetimes" style="width: 310px"/>
                                        <div class="input-group-append">
                                            <button class="btn btn-secondary"><i class="fa fa-calendar-alt "></i></button>
                                        </div>
                                    </div>

                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <table class="table mb-0 table-hover">
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
                            <tr v-for="entity in lists" class="mouse">
                                <td>@{{ entity.div}}</td>
                                <td>@{{ entity.rol}}</td>
                                <td>@{{ entity.token }}</td>
                                <td>@{{ entity.names }}</td>
                                <td>@{{ entity.moment}}</td>
                                <td :class="{'blackr': entity.dend === 'Total'}" >@{{ entity.dend }}</td>
                                <td :class="{'black': entity.dend === 'Total'}" >@{{ entity.hours }}</td>
                            </tr>
                            </tbody>
                        </table>
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
                        <iframe  id="iframe"  src="" frameborder="0" width="100%" height="450px"></iframe>
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
