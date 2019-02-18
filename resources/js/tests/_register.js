require('@babel/register')({
    ignore: ['node_modules/*', 'resources/js/tests/**/*.js'],
    presets: ["@babel/preset-env"]
});
