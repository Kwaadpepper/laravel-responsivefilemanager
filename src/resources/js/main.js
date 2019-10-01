import Vue from "vue";
import App from "./../views/App.vue";
import i18n from "voo-i18n";
import VSwitch from "v-switch-case";
import VueMeta from "vue-meta";

// Object dummy RFM donné par Laravel
import RFMDUMMY from "./DUMSCRIPT.js";

import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import "jplayer/dist/jplayer/jquery.jplayer.min.js";
import "blueimp-file-upload/js/jquery.fileupload.js";
import "blueimp-file-upload/css/jquery.fileupload-ui.css";
import "blueimp-file-upload/css/jquery.fileupload-noscript.css";
import "blueimp-file-upload/css/jquery.fileupload-ui-noscript.css";
import "jquery-contextmenu/dist/jquery.contextMenu.css";

Vue.use(i18n, RFMDUMMY.translations);
Vue.use(VSwitch);
Vue.use(VueMeta);

Vue.config.productionTip = true;

const RFM = RFMDUMMY;

new Vue({
    render: h => h(App),
    data: function() {
        return {
            locale: "fr",
            RFM
        };
    }
}).$mount("#app");
