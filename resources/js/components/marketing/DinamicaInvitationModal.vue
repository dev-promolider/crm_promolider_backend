<template>
    <div>
        <!-- Modal -->
        <b-modal
            id="invitacionModal"
            v-model="mostrarModal"
            title="Enlace de Invitación"
            @hidden="onHidden"
            centered
            size="md"
        >
            <div v-if="enlace">
                <p class="text-muted mb-3">
                    Comparte este enlace para invitar a otros a participar en:
                </p>
                <p class="font-weight-bold mb-3">
                    {{ nombreDinamica }}
                </p>
                
                <div class="input-group">
                    <input 
                        ref="inputEnlace"
                        type="text" 
                        class="form-control" 
                        :value="enlace"
                        readonly
                    >
                    <div class="input-group-append">
                        <button 
                            class="btn btn-outline-primary" 
                            type="button" 
                            @click="copiarAlPortapapeles"
                            :disabled="copiando"
                        >
                            <i :class="copiando ? 'feather icon-check' : 'feather icon-copy'"></i>
                            {{ copiando ? '¡Copiado!' : 'Copiar' }}
                        </button>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">El enlace se copiará en tu portapapeles cuando hagas clic en "Copiar"</small>
                </div>
            </div>

            <template v-slot:modal-footer>
                <b-button 
                    variant="secondary" 
                    @click="cerrarModal"
                >
                    Cerrar
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
export default {
    name: 'DinamicaInvitationModal',
    data() {
        return {
            mostrarModal: false,
            enlace: '',
            nombreDinamica: '',
            copiando: false
        };
    },
    methods: {
        abrirModal(enlace, nombre) {
            this.enlace = enlace;
            this.nombreDinamica = nombre;
            this.mostrarModal = true;
            this.$nextTick(() => {
                if (this.$refs.inputEnlace) {
                    this.$refs.inputEnlace.focus();
                }
            });
        },
        cerrarModal() {
            this.mostrarModal = false;
        },
        onHidden() {
            this.enlace = '';
            this.nombreDinamica = '';
            this.copiando = false;
        },
        copiarAlPortapapeles() {
            const input = this.$refs.inputEnlace;
            if (!input) return;
            
            input.select();
            input.setSelectionRange(0, 99999);
            
            try {
                document.execCommand('copy');
                this.copiando = true;
                
                // Mostrar modal de confirmación
                $('#enlaceCopiado').modal('show');
                
                setTimeout(() => {
                    this.copiando = false;
                }, 2000);
            } catch (err) {
                console.error('Error al copiar:', err);
                alert('No se pudo copiar el enlace');
            }
        }
    }
};
</script>

<style scoped>
.input-group-append {
    margin-left: 0;
}

.btn {
    border-radius: 0 0.25rem 0.25rem 0;
}

.input-group .form-control {
    border-radius: 0.25rem 0 0 0.25rem;
}
</style>
