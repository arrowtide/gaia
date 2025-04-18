// /** @type {import('tailwindcss').Config} */

// export default {
//     content: [
//         './resources/**/**/*.antlers.html',
//         './resources/**/**/*.antlers.php',
//         './resources/**/*.blade.php',
//         './content/**/**/*.md',
//         './content/**/**/*.yaml',
//     ],
//     safelist: [
//         {
//             pattern: /col-span-(\d)/,
//             variants: ['md'],
//         },
//         {
//             pattern: /grid-cols-(\d)/,
//             variants: ['md'],
//         }
//     ],
//     theme: {
//         screens: {
//             'xs':   '420px',
//             'sm':   '600px',
//             'md':   '800px',
//             'lg':   '1000px',
//             'xl':   '1200px',
//             '2xl':  '1500px',
//             '3xl':  '1900px',
//             '3xl-max':  {'max': '1899.98px'},
//             '2xl-max':  {'max': '1499.98px'},
//             'xl-max':   {'max': '1199.98px'},
//             'lg-max':   {'max': '999.98px'},
//             'md-max':   {'max': '799.98px'},
//             'sm-max':   {'max': '599.98px'},
//             'xs-max':   {'max': '419.98px'},
//             'only-hover': { 'raw': '(hover: hover)' },
//         },
//         extend: {
//             padding: {
//                 'nav-gutter': 'var(--space-navigation-gutter)',
//                 'gutter': 'var(--space-gutter)',
//                 'head': 'var(--space-head)',
//                 'safe-bottom': 'env(safe-area-inset-bottom)'

//             },
//             margin: {
//                 'nav-gutter': 'var(--space-navigation-gutter)',
//                 'gutter': 'var(--space-gutter)',
//                 'head': 'var(--space-head)',
//                 'safe-bottom': 'env(safe-area-inset-bottom)'
//             },
//         },
//         fontFamily: {
//             'default': ['Readex Pro', 'fallback', '-apple-system, BlinkMacSystemFont, “Segoe UI”, “Roboto”, “Oxygen”, “Ubuntu”, “Cantarell”, “Fira Sans”, “Droid Sans”, “Helvetica Neue”, sans-serif'],
//         },
//     },
//     plugins: [
//         require("@tailwindcss/forms")({
//             strategy: 'class'
//         }),
//     ],
//     corePlugins: {
//         container: false,
//     },
// };
