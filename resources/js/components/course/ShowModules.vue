<template>
    <div class="card-body">
        Hola mundo
        <h3>Módulo #: {{modulo}}</h3>
        <p>Nombre de la clase: {{clase.nombre}}</p>
    </div>
</template>
<script>
// import show-modules from '@/components/course/ShowModules.vue', components:{show-modules}
export default {
    // name: show-modules,
    data(){
        return{
            modulo: 'modulo del curso',
            clase: {
                nombre: 'nombre de la clase'
            }
        }
    }
}
</script>