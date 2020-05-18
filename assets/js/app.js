/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import '../css/app.scss';

//load the jQuery and import the function from jQuery
const $ = require('jquery');

//load the JS bootstrap part - note that bootstrap doesn't export anything
require('bootstrap');

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
//import $ from 'jquery';

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
