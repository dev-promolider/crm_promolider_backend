<template>
   <v-container >
        <template v-if="!gameData" >
              <div class="h2 text-center">Not found</div>
        </template>

         <template v-if="gameData === 'No hay datos'" >
              <div class="h2 text-center">No Hay Datos</div>
        </template>
       <template v-if="gameData.game">
            <template v-if="gameData.game.game_type === 'Ahorcado'">
                <AhorcadoDinamic :datos="gameData"  />
            </template>
            <template v-else-if="gameData.game.game_type === 'Pares de cartas'">
                <CardsDinamic  :datos="gameData"/>
            </template>
            <template v-else-if="gameData.game.game_type === 'Búho'">
                <OwlQuiz  :datos="gameData"/>
            </template>
            <template v-else-if="gameData.game.game_type === 'Word Wheel'">
                <WordWheel  :datos="gameData"/>
            </template>
       </template>
   </v-container>
</template>

<script>
// import { mapActions } from 'vuex'

import AhorcadoDinamic  from '../components/Ahorcado/Ahorcado.vue';
import CardsDinamic  from '../components/CardsGame/CardsDinamic.vue';
import OwlQuiz from '../components/OwlQuiz/OwlQuiz.vue';
import WordWheel from '../components/wordWheel/WordWheel.vue';

export default {
    name: 'DinamicClass',
    components:{
    AhorcadoDinamic,
    CardsDinamic,
    WordWheel,
    OwlQuiz
},
    props:{
        gameData:Object
    },
    data(){
        return {
            
        }
    },
    created(){
        console.log(this.gameData,this.gameData.game.game_type==='Word Wheel');
       
    }
}
</script>

<style>

</style>