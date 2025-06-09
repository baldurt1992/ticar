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
                .catch(err => {
                    let msg = 'No se pudo acceder a la cámara.';

                    if (err.name === 'NotAllowedError') {
                        msg = 'Por favor, permite el acceso a la cámara en tu navegador.';
                    } else if (err.name === 'NotFoundError') {
                        msg = 'No se encontró ninguna cámara conectada.';
                    } else if (err.name === 'NotReadableError') {
                        msg = 'La cámara está siendo utilizada por otra aplicación.';
                    } else if (err.name === 'OverconstrainedError') {
                        msg = 'No se encontraron cámaras compatibles con los requerimientos.';
                    }

                    this.$toasted.global.error({ message: msg });
                    this.video.style.display = 'none';
                });
        } else {
            this.$toasted.global.error({ message: 'Tu navegador no soporta la cámara.' });
        }

        axios.get(urldomine + 'api/motives/motives').then(res => {
            this.motives = res.data;
        });
    },
    methods: {

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
                        } else {
                            this.user.motive_id = 999;
                            this.user.note = 'Entrada automática al marcar solo salida';
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
            }
        },

        showOb() {
            axios.get(urldomine + 'api/divisions/data/' + this.user.token)
                .then(res => {
                    if (res.data.division.length > 1) {
                        this.divisions = res.data.division;
                        this.pending_check_motive = res.data.pending_check_motive;
                        $('#modalob').modal('show');
                    } else {
                        this.user.division_id = res.data.division[0].id;
                        this.pending_check_motive = res.data.pending_check_motive;
                        $('#modalob').modal('show');
                    }

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
                    if (res.data.division.length > 1) {
                        this.divisions = res.data.division;
                        $('#div').modal('show');
                        this.pending_check_motive = res.data.pending_check_motive;
                    } else {
                        this.user.division_id = res.data.division[0].id;
                        this.pending_check_motive = res.data.pending_check_motive;
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
        check() {
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
                    const msg = res.data;
                    if (typeof msg === 'string' && msg.includes('Todavía no has hecho registro de entrada')) {
                        this.$toasted.show(msg, {
                            className: 'bg-danger text-white',
                            duration: 5000
                        });
                    } else {
                        this.$toasted.global.success({ message: msg, className: 'toast-center-screen bg-success text-white' });
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
                })
                .finally(() => {
                    this.user.token = '';
                    this.user.motive_id = 0;
                    this.user.note = '';
                    this.user.division_id = 0;
                });
        }

    }
});
