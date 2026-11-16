import './bootstrap';

import Alpine from 'alpinejs';
import { registerVariantSearch } from './components/variant-search';

window.Alpine = Alpine;

registerVariantSearch(Alpine);

Alpine.start();
