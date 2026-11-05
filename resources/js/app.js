import Alpine from 'alpinejs';
import { roomBooking, hallBooking } from './booking';
import { landingCalculator } from './landing';

window.Alpine = Alpine;
Alpine.data('roomBooking', roomBooking);
Alpine.data('hallBooking', hallBooking);
Alpine.data('landingCalculator', landingCalculator);
Alpine.start();
