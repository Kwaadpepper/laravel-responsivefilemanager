<template>
    <div class="container-fluid">
        <div class="row-fluid ff-container">
            <Header />
            <BreadCrumb />
            <div class="span12"></div>
            <h1 v-if="$root.RFM.config.upload_files">Toto</h1>
        </div>
    </div>
</template>

<script>
import Header from "./components/Header.vue";
import BreadCrumb from "./components/BreadCrumb.vue";

export default {
    name: "app",
    components: {
        Header,
        BreadCrumb
    },
    data() {
        return {};
    },
    metaInfo: {
        title: "Responsive File Manager",
        script: [
            {
                src: "https://code.jquery.com/ui/1.12.1/jquery-ui.min.js",
                integrity:
                    "sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=",
                crossorigin: "anonymous",
                async: true,
                defer: true
            },
            {
                src:
                    "https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.4.0/fabric.js",
                async: true,
                defer: true
            },
            {
                src:
                    "https://uicdn.toast.com/tui.code-snippet/latest/tui-code-snippet.js",
                async: true,
                defer: true
            },
            {
                src:
                    "https://uicdn.toast.com/tui-color-picker/latest/tui-color-picker.js",
                async: true,
                defer: true
            },
            {
                src:
                    "https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.js",
                async: true,
                defer: true
            }
        ]
    }
};
</script>

<style>
@import "https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.9.2/skin/blue.monday/css/jplayer.blue.monday.css";
@import "https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.css";
@import "https://uicdn.toast.com/tui-color-picker/latest/tui-color-picker.css";
</style>

<style lang="scss">
#app {
    font-family: "Avenir", Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: #2c3e50;
    margin-top: 60px;
}
</style>
