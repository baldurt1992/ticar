<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chequeador Trabajadores</title>
    <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" id="main-stylesheet" data-version="1.1.0"
        href="{{asset('styles/shards-dashboards.1.1.0.min.css')}}">
    <link rel="stylesheet" href="{{asset('styles/extras.1.1.0.min.css')}}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
</head>

<body class="h-100">
    <div class="container-fluid">
        <div class="row">
            <!-- Main Sidebar -->
            <aside class="main-sidebar col-12 col-md-3 col-lg-2 px-0">
                <div class="main-navbar">
                    <nav class="navbar align-items-stretch navbar-light bg-white flex-md-nowrap border-bottom p-0">
                        <a class="navbar-brand w-100 mr-0" href="#" style="line-height: 25px;">
                            <div id="logo-uploader" class="d-table m-auto position-relative"
                                @mouseover="hoverLogo = true" @mouseleave="hoverLogo = false">
                                <img id="main-logo" class="d-inline-block align-top mr-1"
                                    style="max-width: 80px; height: 80px; cursor: pointer;" :src="logoUrl" alt="Logo"
                                    @click="triggerLogoInput" />

                                <input type="file" ref="logoInput" @change="uploadLogo" accept="image/*"
                                    style="display: none;">

                                <i v-if="hoverLogo" class="fas fa-pencil-alt position-absolute"
                                    style="top: -8px; right: -8px; font-size: 12px; color: #888; background: white; border-radius: 50%; padding: 3px; box-shadow: 0 0 3px rgba(0,0,0,0.2); cursor: pointer;"
                                    @click="triggerLogoInput">
                                </i>
                            </div>
                        </a>
                        <a class="toggle-sidebar d-sm-inline d-md-none d-lg-none">
                            <i class="material-icons">&#xE5C4;</i>
                        </a>
                    </nav>
                </div>
                <div class="nav-wrapper">
                    <ul class="nav flex-column">
                        <!-- <li class="nav-item">
                        <a class="nav-link " href="index.html">
                            <i class="material-icons">edit</i>
                            <span>Inicio</span>
                        </a>
                    </li> -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('company')}}">
                                <i class="material-icons">account_balance</i>
                                <span>Empresa</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('divisions')}}">
                                <i class="material-icons">home</i>
                                <span>Sucursales</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('persons')}}">
                                <i class="material-icons">supervised_user_circle</i>
                                <span>Personal</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('rols')}}">
                                <i class="material-icons">subject</i>
                                <span>Roles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="{{route('users')}}">
                                <i class="material-icons">account_box</i>
                                <span>Usuarios</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="{{route('motives')}}">
                                <i class="material-icons">speaker_notes</i>
                                <span>Motivos</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link " href="{{route('report')}}">
                                <i class="material-icons">alarm</i>
                                <span>Reportes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('custom.reports') }}">
                                <i class="material-icons">insert_chart</i>
                                <span>Reportes Personalizados</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>
            <!-- End Main Sidebar -->
            <main class="main-content col-lg-10 col-md-9 col-sm-12 p-0 offset-lg-2 offset-md-3">
                <div class="main-navbar sticky-top bg-white">
                    <!-- Main Navbar -->
                    <nav class="navbar align-items-stretch navbar-light flex-md-nowrap p-0 justify-content-end">
                        <ul class="navbar-nav border-left flex-row ">
                            <li class="nav-item border-right dropdown notifications">
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-nowrap px-3" data-toggle="dropdown" href="#"
                                    role="button" aria-haspopup="true" aria-expanded="false">
                                    <img width="60px" class="rounded-circle mr-2" src="{{asset('images/ava.jpg')}}"
                                        alt="User Avatar">
                                    <span class="d-none d-md-inline-block">{{ Auth::user()->name }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-small">
                                    <!-- <a class="dropdown-item" href="user-profile-lite.html">
                                    <i class="material-icons">&#xE7FD;</i> Perfil</a>
                                <a class="dropdown-item" href="components-blog-posts.html">
                                    <i class="material-icons">vertical_split</i> Ajustes</a>
                                <div class="dropdown-divider"></div> -->
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="material-icons text-danger">&#xE879;</i> Salir
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </ul>
                        <nav class="nav">
                            <a href="#"
                                class="nav-link nav-link-icon toggle-sidebar d-md-inline d-lg-none text-center border-left"
                                data-toggle="collapse" data-target=".header-navbar" aria-expanded="false"
                                aria-controls="header-navbar">
                                <i class="material-icons">&#xE5D2;</i>
                            </a>
                        </nav>
                    </nav>
                </div>
                <!-- / .main-navbar -->
                <div class="main-content-container container-fluid px-4" id="app" v-cloak>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script src="{{asset('appjs/lodash.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
        integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"></script>
    <script src="{{asset('scripts/shards-dashboards.1.1.0.min.js')}}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="{{asset('appjs/toasted.min.js')}}"> </script>
    <script src="{{asset('appjs/vue-toasted.js')}}"> </script>
    <script src="{{asset('appjs/tools.js')}}"></script>
    <script>
        Vue.use(Toasted, {
            duration: 5000,
            position: 'top-center',
            theme: 'toasted',
        });

        Vue.toasted.register('success', payload => payload.message || 'Éxito', {
            className: 'bg-success text-white',
            duration: 5000
        });

        Vue.toasted.register('error', payload => payload.message || 'Error', {
            className: 'bg-danger text-white',
            duration: 5000
        });

        new Vue({
            el: '#logo-uploader',
            data() {
                return {
                    hoverLogo: false,
                    logoUrl: '{{ asset("storage/logos/" . (\App\Company::first()->logo ?? "default.webp")) }}'
                }
            },
            methods: {
                triggerLogoInput() {
                    this.$refs.logoInput.click();
                },
                uploadLogo(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (file.size > 5 * 1024 * 1024) {
                        this.$toasted.global.error({ message: "La imagen es demasiado grande. Máximo 5MB." });
                        return;
                    }

                    const formData = new FormData();
                    formData.append('logo', file);

                    axios.post("{{ route('company.uploadLogo') }}", formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    }).then(res => {
                        if (res.data.logo_url) {
                            this.logoUrl = res.data.logo_url + '?' + new Date().getTime();
                            this.$toasted.global.success({ message: "Logo actualizado correctamente." });
                        }
                    }).catch(err => {
                        if (err.response && err.response.status === 413) {
                            this.$toasted.global.error({ message: "La imagen es demasiado grande. Máximo 5MB." });
                        } else {
                            this.$toasted.global.error({ message: "Error al subir el logo." });
                        }
                    });
                }
            }
        });
    </script>
    @yield('script')
</body>

</html>