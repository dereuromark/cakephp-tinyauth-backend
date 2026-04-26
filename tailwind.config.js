/**
 * Tailwind config for cakephp-tinyauth-backend.
 *
 * Run `composer assets` (or directly `npx tailwindcss@^3 -i webroot/css/tailwind.input.css -o webroot/css/tailwind.css --minify`)
 * to regenerate `webroot/css/tailwind.css` whenever templates or JS change the
 * set of utility classes in use. The generated file is committed so end users
 * do not need any Node tooling.
 */
module.exports = {
    content: [
        './templates/**/*.php',
        './webroot/js/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: '#3b82f6',
            },
        },
    },
};
