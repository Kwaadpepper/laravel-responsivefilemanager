module.exports = {
    pages: {
        index: {
            // entry for the page
            entry: "src/resources/js/main.js"
        }
    },

    configureWebpack: config => {
        if (process.env.NODE_ENV === "production") {
            // mutate config for production...
        } else {
            // mutate for development...
        }
    },

    filenameHashing: false,
    runtimeCompiler: true
};
