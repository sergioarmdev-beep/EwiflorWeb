import './bootstrap';

import Alpine from 'alpinejs';
import { registerComboForm } from './components/combo-form';
import { registerPromocionForm } from './components/promocion-form';
import { registerVariantSearch } from './components/variant-search';

window.Alpine = Alpine;

registerComboForm(Alpine);
registerPromocionForm(Alpine);
registerVariantSearch(Alpine);

Alpine.start();
