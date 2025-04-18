import { Livewire, Alpine } from '/vendor/livewire/livewire/dist/livewire.esm';
import Defer from 'alpinejs-defer';
import Precognition from 'laravel-precognition-alpine';

/**
 * Statamic form conditions
 * 
 * If need to use Statamics form conditions, you can uncomment this
 * import.
 */

// import '/vendor/statamic/cms/resources/dist-frontend/js/helpers';


// Alpine directives and plugins
Alpine.directive('defer', Defer).before('ignore');
Alpine.plugin(Precognition);

// Make Alpine and Livewire available globally
window.Alpine = Alpine;
window.Livewire = Livewire;
