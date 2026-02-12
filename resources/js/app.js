import './bootstrap';
import { OfflineSync } from './offline-sync';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.OfflineSync = new OfflineSync();

Alpine.start();
