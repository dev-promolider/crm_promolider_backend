<template>
    <div>
        <section>
            <div class="ml-2" >
                <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Certificados:</label>
                                        <el-select v-model="template_save"
                                            placeholder="Seleccione un certificado" class="d-inline">
                                            <el-option v-for="item in this.options" 
                                                :key="item.value" 
                                                :label="item.label"
                                                :value="item.value">
                                            </el-option>
                                        </el-select>
                                    </div>
                                </div>
                                <div class="col-lg-6 mt-2">
                                    <div class="form-group">
                                        <div class="mb-4">
                                            <button type="submit" class="btn btn-primary" @click="openCertificate()">
                                                Previsualizar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                        <label>Subir Firma</label>
                                    <el-upload 
                                        lass="picture-card" 
                                        ref="courseUpload" action="" 
                                        drag
                                        accept=".jpg,.jpeg,.png,.JPG,.JPEG,.PNG" 
                                        :on-change="onUploadChange" 
                                        :multiple="false" 
                                        :limit="1" 
                                        :auto-upload="false" 
                                        :file-list="fileList"
                                        :on-exceed="handleExceed" 
                                        :on-remove="handleRemove" 
                                        list-type="picture">

                                            <i class="el-icon-upload"></i>
                                            <div class="el-upload__text">
                                                Suelta tu archivo aquí o <em>haz clic para cargar</em>
                                            </div>

                                            <div slot="tip" class="el-upload__tip">
                                                Solo archivos jpg/png con un tamaño menor de 100kb
                                            </div>

                                    </el-upload>
                                </div>
                                <div class="col-lg-12 mt-2">
                                    <div class="form-group">
                                        <div class="mb-4">
                                            <button type="submit" class="btn btn-primary"
                                                @click="createCertificeConfig()">
                                                Guardar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="d-flex justify-content-center" style="width: 100%;" v-html="template">
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </section>
    </div>
</template>
<script>
import api from '../../../api/api';

export default {
    name: 'CertificateSetting',
    components: {
    },

    data() {
        return {
            initialLoading: false,
            requestLoading: false,
            certificates: [],
            certificates_options: [],
            options: [{}],
            formCertificate: {},
            template: '',
            url_imagen: '',
            errors_back: '',
            fileList: [],
            template_save: '',
            img_signature:'',
        };
    },
    mounted() {
        this.listCertificates();
        this.fillFiles();
    },

    methods: {
        fillFiles() {
            if(this.img_signature != ''){
                this.fileList = [
                {   
                    name: `${this.getNameSignature(this.img_signature)}`,
                    url: `https://promolider-storage-user.s3-accelerate.amazonaws.com/${this.img_signature}?a=${Math.random()}`,
                },
                ];
            }
        },
        getNameSignature(signature){
            let rutaSignature = signature.split("/");
            return rutaSignature[(rutaSignature.length - 1)];
        },
        listCertificates: function () {
            this.initialLoading = true;
            api.get(`/config/certificates/list`).then((response) => {
                this.certificates = response.data[0];
                //validar si existe un certificado almacenado
                if(response.data[2][0] != undefined ){
                    this.template_save = Number(response.data[2][0].value);
                }
                //validar si existe una imagen guardada
                if(response.data[1][0] != undefined ){
                    this.img_signature = response.data[1][0].value;
                }
                this.fillFiles();
                this.initialLoading = false;
                //agregar a las opciones select
                for (var i = 0; i < response.data[0].length; i++) {
                    this.certificates_options.push({
                        value: response.data[0][i].id,
                        label: response.data[0][i].name,
                    });
                }
                this.options = this.certificates_options;
                
            });
        },
        //obtener la opcion seccionada
        openCertificate() {
            this.formCertificate.option_certificate = this.template_save;
            if(this.formCertificate.option_certificate != undefined) {
                const objtRes = this.certificates.find(obj => obj.id === this.formCertificate.option_certificate);
                this.template = objtRes.template;
            }
            else{
                this.$message.warning("Seleccione un certificado");
            }
        },
        createCertificeConfig() {
            this.formCertificate.option_certificate = this.template_save;
            let idTemplate = -1;
            if (this.formCertificate.option_certificate != undefined) {
                const objtRes = this.certificates.find(obj => obj.id === this.formCertificate.option_certificate);
                idTemplate = objtRes.id;
            }
            
            if(idTemplate == -1 && this.url_imagen == ""){
                this.$message.warning("Seleccione un certificado o una imagen");
                return false;
            }

            const formdata = new FormData();
            formdata.append('id', idTemplate);
            formdata.append('signature', this.url_imagen );

            api.post(`/certificates/save`, formdata).then((response) => {

                if (response == 'ok') {
                    this.$message.success('Registro guardado correctamente');
                }else {
                    this.$message.warning('Error al realizar la acción');
                }

            }).catch((error) => {
                this.$message.warning('Error al realizar la acción');
            });
            
        },
        //LIMITACION DE IMAGEN
        handleExceed(file) {
            if (this.fileList.length >= 1) {
                this.$message.error('Solo puede seleccionar 1 archivo!');
                return false;
            }

            const isLt1M = file.size < 100000;

            if (!isLt1M) {
                this.$message.error('El archivo no debe pesar más de 100kb!');
                return false;
            }
        },
        //
        onUploadChange(file) {
            this.fileList = [];

            const isImage =
                file.raw.type === 'image/jpeg' ||
                file.raw.type === 'image/png' ||
                file.raw.type === 'image/jpg';

            const isLt1M = file.size < 100000;

            if (!isImage) {
                this.$message.error('Solo puede cargar archivos en formato jpg|jpeg|png.');
                this.$refs.courseUpload.clearFiles();
                return false;
            }
            if (!isLt1M) {
                this.$message.error('El archivo no debe pesar más de 100kb!');
                this.$refs.courseUpload.clearFiles();
                return false;
            }

            this.fileList.push(file);
            try {
                this.url_imagen = this.fileList[0].raw;
            } catch (error) {
                console.error(error);
            }

        },
        handleRemove(fileremoved, file) {
            this.fileList = file;
            this.url_imagen = "";
        },
    },
};
</script>

<style>
</style>
