Vue.use(Toasted, toast_options);

new Vue({
    el: '#app',
    data() {
        return {
            tiempo: 1,
            semana: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
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
        }
    },
    mounted() {
        setInterval(() => {
            this.reloj()
        }, 1000);
        this.fecha();
        this.video = document.querySelector("#camara");
        this.canvas = document.getElementById("canvas");
        if (this.soporteUserMedia()) {
            this._getUserMedia({ video: true }, (stream) => { this.video.srcObject = stream; }, er => {
                this.$toasted.show(er, toast_options)
            });
        } else {
            this.$toasted.show('Lo siento. El navegador no soporta esta característica', toast_options)
        }

        axios.get(urldomine + 'api/motives/motives').then(res => {
            this.motives = res.data
        });

    },
    methods: {
        vals(e) {

            if (e.key === 'Enter') {
                this.checkd()
            }

        },
        showOb() {
            axios.get(urldomine + 'api/divisions/data/' + this.user.token).then(res => {

                if (res.data.division.length > 1) {
                    this.divisions = res.data.division;
                    this.pending_check_motive = res.data.pending_check_motive

                    $('#modalob').modal('show');
                } else {
                    this.user.division_id = res.data.division[0].id;
                    this.pending_check_motive = res.data.pending_check_motive
                    $('#modalob').modal('show');
                }
                if (this.pending_check_motive > 0) {
                    this.user.motive_id = this.pending_check_motive
                    $('#block_motives').attr('disabled', true);
                    $('#block_motives').val(this.pending_check_motive);
                    $('#block_motives option[value="' + this.pending_check_motive + '"]').prop('selected', true);
                } else {
                    $('#block_motives').attr('disabled', false);
                    $('#block_motives').val('');
                }

            }).catch(er => {
                this.$toasted.show(er.response.data)
            });

        },
        setNCodigo(x) {
            this.user.token += x;
        },
        deteteNBAN() {
            this.user.token = ''
        },
        deteteNCodigo() {
            this.user.token = this.user.token.substring(0, this.user.token.length - 1)
        },
        soporteUserMedia() {
            return !!(navigator.getUserMedia || navigator.mediaDevices.getUserMedia || navigator.webkitGetUserMedia || navigator.msGetUserMedia)
        },
        _getUserMedia() {
            return (navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia).apply(navigator, arguments);
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
            axios.get(urldomine + 'api/divisions/data/' + this.user.token).then(res => {
                if (res.data.division.length > 1) {
                    this.divisions = res.data.division;
                    $('#div').modal('show');
                    this.pending_check_motive = res.data.pending_check_motive
                } else {

                    this.user.division_id = res.data.division[0].id;
                    this.pending_check_motive = res.data.pending_check_motive
                    this.check()
                }

            }).catch(er => {
                this.$toasted.show(er.response.data)
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
            axios({
                url: urldomine + 'api/persons/check',
                method: 'post',
                data: this.user,
            }).then(res => {
                this.$toasted.show(res.data, toast_options);
                this.user.token = '';
                this.user.motive_id = 0;
                this.user.note = '';
                this.user.screen = 0;
                $('#div').modal('hide');
                $('#modalob').modal('hide');
            }).catch(er => {
                this.$toasted.show(er.response.data, toast_options)
            })

        }
    }

});
