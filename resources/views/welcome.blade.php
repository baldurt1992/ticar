<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
        integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <link rel="stylesheet" id="main-stylesheet" data-version="1.1.0"
        href="{{asset('styles/shards-dashboards.1.1.0.min.css')}}">
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('styles/extras.1.1.0.min.css')}}">
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <link rel="stylesheet" href="{{asset('css/main.css')}}">

</head>

<body>
    <div id="app" v-cloak>
        <div class="container containerA mt-3">
            <div class="flex-row justify-content-center">
                <div class="card">
                    <div class="card-header pb-0">
                        <p class="fecha mb-0">@{{ diaSemana }} @{{ dia }} de @{{ mes }} del @{{ anio }} </p>
                    </div>
                    <div class="card-body mb-3">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <p class="tiempo">@{{ tiempo }} </p>

                            <video autoplay="true" class="form-control text-center fecha" id="camara"></video>
                        </div>
                        <div class="mt-3 mb-3 keypad">
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="0"
                                @click="setNCodigo(0)">0</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="1"
                                @click="setNCodigo(1)">1</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="2"
                                @click="setNCodigo(2)">2</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="3"
                                @click="setNCodigo(3)">3</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="4"
                                @click="setNCodigo(4)">4</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="5"
                                @click="setNCodigo(5)">5</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="6"
                                @click="setNCodigo(6)">6</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="7"
                                @click="setNCodigo(7)">7</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="8"
                                @click="setNCodigo(8)">8</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="9"
                                @click="setNCodigo(9)">9</button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="del"
                                @click="deteteNCodigo()"><i class="fa fa-eraser"></i></button>
                            <button class="btn btn-info btn-lg mb-1 keypad-button" data-key="ban"
                                @click="deteteNBAN()"><i class="fa fa-ban"></i></button>
                        </div>
                        <div class="form-control-wrapper">
                            <input type="text" style="border: 2px solid red"
                                class="form-control form-control-input text-center fecha" placeholder="CODIGO EMPLEADO"
                                v-model="user.token" @keydown="vals($event)" readonly="readonly">
                        </div>
                        <div
                            class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mt-2 mb-2 d-flex flex-wrap justify-content-center entry-buttons">
                            <button :disabled="user . token . length <= 0" class="btn btn-success btn-lg"
                                @click="openConfirm('entrada')">ENTRAR</button>
                            <button :disabled="user . token . length <= 0" class="btn btn-danger btn-lg"
                                @click="openConfirm('salida')">SALIR</button>
                            <button :disabled="!entradaNormalActiva" class="btn btn-warning btn-lg" @click="showOb()"
                                :title="!entradaNormalActiva
        ? 'Debes marcar una entrada antes de registrar OTROS'
        : (pending_check_motive > 0
            ? 'Ya tienes una entrada abierta con motivo ' + getMotivoNombre(pending_check_motive)
            : '')">
                                OTROS
                            </button>


                            <canvas id="canvas" style="display: none;"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div id="modalob" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog " role="document">
                <div class="modal-content" style="min-height: 60vh;">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title" style="color: black" id="exampleModalLabel">Sucursal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Motivos
                        <select class="form-control custom-select" v-model="user.motive_id" id="block_motives">
                            <option v-for="mot in  motives" :value="mot . id">@{{ mot.motive }}</option>
                        </select>
                        Nota
                        <textarea class="form-control" v-model="user.note" rows="5"></textarea>
                        <div class="custom-controls-stacked mt-5">
                            <div v-for="div in divisions" class="custom-control custom-radio mb-3">
                                <input type="radio" :id="'c' + div . id" name="customRadio" :value="div . id"
                                    class="custom-control-input" v-model="user.division_id">
                                <label class="custom-control-label" :for="'c' + div . id">@{{ div.names }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button :disabled="user . division_id === 0" @click="actionType = 'entrada'; check()"
                            class="btn btn-success btn-lg w-50">
                            ENTRAR
                        </button>
                        <a href="#" data-dismiss="modal" class="btn btn-secondary btn-lg w-50">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-registro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title" style="color: black" id="exampleModalLabel">Sucursal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="mensajeModal">Mensaje de alerta</p>
                        <button id="btnActualizarEntrada" class="btn btn-success">Actualizar Entrada</button>
                        <button id="btnRegistrarSalida" class="btn btn-warning">Registrar Salida</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-dark">Confirmar acción</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessage">¿Estás seguro?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" @click="confirmAction">Sí</button>
                        <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-otros-activo" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="modalOtrosActivoLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark" id="modalOtrosActivoLabel">Motivo OTROS activo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-dark">
                        <p id="mensaje-otros-activo"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" @click="marcarRegreso">Marcar
                            regreso</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script
        src="https://code.jquery.com/jquery-3.3.1.min.js"> integrity = "sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin = "anonymous" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"
        integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"
        integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k"
        crossorigin="anonymous"></script>
    <script src="{{asset('appjs/tools.js')}}"> </script>
    <script src="{{asset('appjs/toasted.min.js')}}"> </script>
    <script src="{{asset('appjs/vue-toasted.js')}}"> </script>
    <script src="{{asset('appjs/camera.js')}}"> </script>
    <script>
            $('#modal-registro').modal('hide');
    </script>

</body>

</html>