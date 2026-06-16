<template>
    <div>
        <template v-if="loading">
            <div class="text-center">
                <v-progress-circular indeterminate color="success"> </v-progress-circular>
            </div>
        </template>
        <VueUnity v-if="!loading" class="unity-canvas rounded-0" :unity="unityContext" :width="'100%'"
            :height="'50vh'" />
        <br /><br />
        <!-- <v-simple-table dark>
        <template v-slot:default>
            <thead>
                <tr>
                    <th class="text-left">
                        #
                    </th>
                    <th class="text-left">
                        Alumno
                    </th>
                    <th class="text-left">
                        Puntaje
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Tony</td>
                    <td>4500</td>
                </tr>
            </tbody>
        </template>
    </v-simple-table> -->
    </div>
</template>

<script>
import UnityWebgl from 'unity-webgl';
import VueUnity from 'unity-webgl/vue';

const Unity = new UnityWebgl({
    loaderUrl: '/Build/owlQuiz2/final2.loader.js',
    dataUrl: '/Build/owlQuiz2/final2.data',
    frameworkUrl: '/Build/owlQuiz2/final2.framework.js',
    codeUrl: '/Build/owlQuiz2/final2.wasm',
});

Unity.on('device', () => alert('click device ...'));

export default {
    props: ['datos'],
    components: {
        VueUnity,
    },
    data() {
        return {
            unityContext: Unity,
            loading: true,
            data: null,
        };
    },
    mounted() {
        this.cargarDatos();
    },
    methods: {
        async cargarDatos() {
            // await this.axios.get(`/course/dinamicas/datos/${this.$route.query.gameid}`).then((r) => {

            if (this.datos.detail.length == 0) {
                this.loading = false;
                this.data = [];
                alert('no hay datos');
            } else {
                this.data = JSON.stringify({ data: [...this.datos.detail] });
                this.loading = false;
                setTimeout(this.consumirAPI, 10000);
            }
        },

        consumirAPI() {
            Unity.send('JavaScriptJson', 'setString', this.data);
        },
    },
};
// var container = document.querySelector("#unity-container");
// var canvas = document.querySelector("#unity-canvas");
// var loadingBar = document.querySelector("#unity-loading-bar");
// var progressBarFull = document.querySelector("#unity-progress-bar-full");
// var fullscreenButton = document.querySelector("#unity-fullscreen-button");
// var warningBanner = document.querySelector("#unity-warning");

// function unityShowBanner(msg, type) {
// function updateBannerVisibility() {
//     warningBanner.style.display = warningBanner.children.length ? 'block' : 'none';
// }
// var div = document.createElement('div');
// div.innerHTML = msg;
// warningBanner.appendChild(div);
// if (type == 'error') div.style = 'background: red; padding: 10px;';
// else {
//     if (type == 'warning') div.style = 'background: yellow; padding: 10px;';
//     setTimeout(function() {
//     warningBanner.removeChild(div);
//     updateBannerVisibility();
//     }, 5000);
// }
// updateBannerVisibility();
// }

// var buildUrl = "Build/treasureHunter";
// var loaderUrl = buildUrl + "/Treasure Hunter.loader.js";
// var config = {
// dataUrl: buildUrl + "/Treasure Hunter.data",
// frameworkUrl: buildUrl + "/Treasure Hunter.framework.js",
// codeUrl: buildUrl + "/Treasure Hunter.wasm",
// streamingAssetsUrl: "StreamingAssets",
// companyName: "CHEGO_WEP",
// productName: "Treasure Hunter",
// productVersion: "1.0",
// showBanner: unityShowBanner,
// };

// if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
// // Mobile device style: fill the whole browser client area with the game canvas:

// var meta = document.createElement('meta');
// meta.name = 'viewport';
// meta.content = 'width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, shrink-to-fit=yes';
// document.getElementsByTagName('head')[0].appendChild(meta);
// container.className = "unity-mobile";

// // To lower canvas resolution on mobile devices to gain some
// // performance, uncomment the following line:
// // config.devicePixelRatio = 1;

// canvas.style.width = window.innerWidth + 'px';
// canvas.style.height = window.innerHeight + 'px';

// unityShowBanner('WebGL builds are not supported on mobile devices.');
// } else {
// // Desktop style: Render the game canvas in a window that can be maximized to fullscreen:

// canvas.style.width = "1280px";
// canvas.style.height = "720px";
// }

// loadingBar.style.display = "block";

// var script = document.createElement("script");
// script.src = loaderUrl;
// script.onload = () => {
// createUnityInstance(canvas, config, (progress) => {
//     progressBarFull.style.width = 100 * progress + "%";
// }).then((unityInstance) => {
//     loadingBar.style.display = "none";
//     fullscreenButton.onclick = () => {
//     unityInstance.SetFullscreen(1);
//     };
// }).catch((message) => {
//     alert(message);
// });
// };
// document.body.appendChild(script);
</script>
