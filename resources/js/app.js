import Alpine from 'alpinejs';
import { createIcons,icons } from 'lucide';

// Make Alpine globally accessible
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// Initialize Lucide icons
// Since Vite loads modules with type="module" (deferred), the DOM is already
// loaded by the time this code runs, so we can call createIcons() directly.
createIcons({icons});

// Re-render Lucide icons after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    createIcons( {icons});
});

// Re-render Lucide icons after Livewire loads
document.addEventListener('livewire:load', () => {
    createIcons({icons});
});

// Bascule de thème manuelle (au cas où Alpine n'est pas encore prêt)
document.addEventListener('DOMContentLoaded', () => {
    createIcons({icons});
});