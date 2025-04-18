import Swiper from 'swiper';
import { Navigation, Pagination, Scrollbar, Zoom, Thumbs } from 'swiper/modules';

Swiper.use([Navigation, Pagination, Scrollbar, Zoom, Thumbs]);

// Make Swiper globally available
window.Swiper = Swiper;

// Emit a custom event when Swiper has loaded so we can use it 
// in our scripts.
document.dispatchEvent(new CustomEvent('swiper:loaded'));
