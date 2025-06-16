Vue.use(Toasted, {
    duration: 5000,
    position: 'top-center',
    theme: 'toasted'
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
    el: '#app',
    data() {
        return {
            entradaNormalActiva: false,
            entradaOtrosActiva: false,
            entradaOtrosMotivo: '',
            tiempo: 1,
            semana: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            meses: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            diaSemana: '',
            dia: '',
            mes: '',
            anio: '',
            video: '',
            canvas: '',
            user: {
                token: '',
                ob: false,
                motive_id: 0,
                note: '',
                screen: 0,
                division_id: 0
            },
            motives: [],
            divisions: [],
            pending_check_motive: 0,
            actionType: '',
        };
    },
    mounted() {
        setInterval(() => this.reloj(), 1000);
        this.fecha();
        this.video = document.querySelector("#camara");
        this.canvas = document.getElementById("canvas");

        if (navigator.mediaDevices?.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    this.video.srcObject = stream;
                })
                .catch(() => {
                    this.video.style.display = 'none';
                });
        } else {
            this.$toasted.global.error({ message: 'Tu navegador no soporta la cámara.' });
        }

        axios.get(urldomine + 'api/motives/motives').then(res => {
            this.motives = res.data;
        });

        this.validarEntradaNormal();
    },
    methods: {
        actualizarPendingMotive() {
            if (!this.user.token) return;

            axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                .then(res => {
                    this.pending_check_motive = res.data.pending_check_motive || 0;
                });
        },

        getMotivoNombre(id) {
            const mot = this.motives.find(m => m.id === id);
            return mot ? mot.motive : 'desconocido';
        },

        validarEntradaNormal() {
            if (!this.user.token) {
                this.entradaNormalActiva = false;
                return;
            }

            axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                .then(res => {
                    this.entradaNormalActiva = res.data.has_open_check === true;
                })
                .catch(() => {
                    this.entradaNormalActiva = false;
                });
        },

        vals(e) {
            if (e.key === 'Enter') {
                this.checkd();
            }
        },

        openConfirm(tipo) {
            this.actionType = tipo;
            document.getElementById('confirmMessage').innerText =
                tipo === 'entrada' ? '¿Deseas marcar la ENTRADA?' : '¿Deseas marcar la SALIDA?';
            $('#confirmModal').modal('show');
        },

        confirmAction() {
            $('#confirmModal').modal('hide');

            if (this.actionType === 'entrada') {
                this.user.motive_id = 0;
                this.user.note = '';
                this.check();
            } else {
                axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                    .then(res => {
                        if (res.data.division.length > 0) {
                            this.user.division_id = res.data.division[0].id;
                        } else {
                            this.$toasted.global.error({ message: 'No se encontraron divisiones asociadas al usuario.' });
                            return;
                        }

                        this.pending_check_motive = res.data.pending_check_motive;

                        if (res.data.has_open_check) {
                            this.user.motive_id = 0;
                            this.user.note = '';
                            this.check();

                            setTimeout(() => {
                                this.actualizarPendingMotive();
                                this.validarEntradaNormal();
                            }, 1000);
                        } else {
                            this.user.motive_id = 999;
                            this.user.note = 'Entrada automática al marcar solo salida';
                            this.check();

                            setTimeout(() => {
                                this.actualizarPendingMotive();
                                this.validarEntradaNormal();
                            }, 1000);
                        }
                    })
                    .catch(er => {
                        let msg = 'Error inesperado';
                        if (typeof er.response?.data === 'string') {
                            msg = er.response.data;
                        } else if (er.response?.data?.message) {
                            msg = er.response.data.message;
                        } else if (er.response?.data?.errors) {
                            const errores = er.response.data.errors;
                            const primerCampo = Object.keys(errores)[0];
                            msg = errores[primerCampo][0];
                        }

                        this.$toasted.global.error({ message: msg, className: 'toast-center-screen bg-danger text-white' });
                    });
            }
        },

        marcarRegreso() {
            $('#modal-otros-activo').modal('hide')

            this.actionType = 'salida';
            this.user.motive_id = this.pending_check_motive;
            this.user.note = '(regreso)';

            axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                .then(res => {
                    if (res.data.division.length > 0) {
                        this.user.division_id = res.data.division[0].id;

                        this.check();
                    } else {
                        this.$toasted.global.error({ message: 'No se encontraron divisiones para marcar regreso.' });
                    }
                });
        },

        showOb() {
            axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                .then(res => {
                    this.pending_check_motive = res.data.pending_check_motive || 0;

                    if (this.pending_check_motive > 0) {
                        const motivo = this.getMotivoNombre(this.pending_check_motive);
                        document.getElementById('mensaje-otros-activo').innerText =
                            `Ya tienes una entrada registrada con motivo "${motivo}", ¿quieres marcar el regreso?`;
                        $('#modal-otros-activo').modal('show');
                        return;
                    }

                    if (!res.data.has_open_check) {
                        this.$toasted.global.error({ message: 'Debes tener una entrada activa para registrar un motivo OTROS.' });
                        return;
                    }

                    if (res.data.division.length > 1) {
                        this.divisions = res.data.division;
                    } else {
                        this.user.division_id = res.data.division[0].id;
                    }

                    $('#modalob').modal('show');

                    if (this.pending_check_motive > 0) {
                        this.user.motive_id = this.pending_check_motive;
                        $('#block_motives').attr('disabled', true).val(this.pending_check_motive);
                        $('#block_motives option[value="' + this.pending_check_motive + '"]').prop('selected', true);
                    } else {
                        $('#block_motives').attr('disabled', false).val('');
                    }
                })
                .catch(er => {
                    let msg = 'Error inesperado';

                    if (typeof er.response?.data === 'string') {
                        msg = er.response.data;
                    } else if (er.response?.data?.message) {
                        msg = er.response.data.message;
                    } else if (er.response?.data?.errors) {
                        const errores = er.response.data.errors;
                        const primerCampo = Object.keys(errores)[0];
                        msg = errores[primerCampo][0];
                    }

                    this.$toasted.global.error({ message: msg, className: 'toast-center-screen bg-danger text-white' });
                });
        },
        setNCodigo(x) {
            this.user.token += x;
        },
        deteteNBAN() {
            this.user.token = '';
        },
        deteteNCodigo() {
            this.user.token = this.user.token.slice(0, -1);
        },
        fecha() {
            let fecha = new Date();
            this.diaSemana = this.semana[fecha.getDay()];
            this.mes = this.meses[fecha.getMonth()];
            this.anio = fecha.getFullYear();
            this.dia = fecha.getDate();
        },
        reloj() {
            this.tiempo = new Date().toLocaleTimeString();
        },
        checkd() {
            axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                .then(res => {
                    this.pending_check_motive = res.data.pending_check_motive;

                    if (res.data.division.length > 1) {
                        this.divisions = res.data.division;
                        $('#div').modal('show');
                    } else {
                        this.user.division_id = res.data.division[0].id;

                        this.actionType = (this.user.motive_id > 0 || this.pending_check_motive > 0)
                            ? 'entrada'
                            : 'salida';

                        this.check();
                    }
                })
                .catch(er => {
                    let msg = 'Error inesperado';

                    if (typeof er.response?.data === 'string') {
                        msg = er.response.data;
                    } else if (er.response?.data?.message) {
                        msg = er.response.data.message;
                    } else if (er.response?.data?.errors) {
                        const errores = er.response.data.errors;
                        const primerCampo = Object.keys(errores)[0];
                        msg = errores[primerCampo][0];
                    }

                    this.$toasted.global.error({ message: msg, className: 'toast-center-screen bg-danger text-white' });
                });
        },

        check(cerrarModalOtros = false) {
            this.video.pause();
            let contexto = this.canvas.getContext("2d");
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;
            contexto.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);
            this.video.play();

            this.user.screen = this.canvas.toDataURL();

            axios.post(urldomine + 'api/persons/check', {
                ...this.user,
                accion: this.actionType
            })
                .then(res => {
                    let mensaje = res.data;
                    if (
                        this.user.motive_id > 0 &&
                        this.actionType === 'entrada' &&
                        res.data.startsWith('Entrada registrada con éxito')
                    ) {
                        const motivo = this.getMotivoNombre(this.user.motive_id);
                        mensaje = `Entrada registrada con motivo "${motivo}". Entrada generada.`;
                    } else if (
                        this.user.motive_id > 0 &&
                        this.actionType === 'salida' &&
                        res.data.startsWith('Salida registrada con éxito')
                    ) {
                        const motivo = this.getMotivoNombre(this.user.motive_id);
                        mensaje = `Salida registrada con motivo "${motivo}".`;
                    }

                    if (this.user.motive_id > 0 && this.actionType === 'entrada') {
                        $('#modalob').modal('hide');
                    }

                    this.$toasted.global.success({
                        message: mensaje,
                        className: 'toast-center-screen bg-success text-white'
                    });
                })
                .catch(er => {
                    let msg = 'Error inesperado';
                    if (typeof er.response?.data === 'string') {
                        msg = er.response.data;
                    } else if (er.response?.data?.message) {
                        msg = er.response.data.message;
                    } else if (er.response?.data?.errors) {
                        const errores = er.response.data.errors;
                        const primerCampo = Object.keys(errores)[0];
                        msg = errores[primerCampo][0];
                    }

                    this.$toasted.global.error({ message: msg, className: 'toast-center-screen bg-danger text-white' });
                })
                .finally(() => {
                    this.user.token = '';
                    this.user.motive_id = 0;
                    this.user.note = '';
                    this.user.division_id = 0;

                    setTimeout(() => {
                        this.actualizarPendingMotive();
                    }, 300);
                });
        }
    },

    watch: {
        'user.token'(val) {
            if (val && val.length >= 2) {
                this.validarEntradaNormal();
            } else {
                this.entradaNormalActiva = false;
            }
        }
    }

});
