import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './notificationDropdown';
import { createIcons, icons } from 'lucide';

createIcons({ icons });

// Make Alpine globally accessible
window.Alpine = Alpine;

Alpine.plugin(collapse);

// Start Alpine
Alpine.start();

// Re-render Lucide icons after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    createIcons({icons});
});

// Re-render Lucide icons after Livewire loads
document.addEventListener('livewire:load', () => {
    createIcons({icons});
});

// Bascule de thème manuelle (au cas où Alpine n'est pas encore prêt)
document.addEventListener('DOMContentLoaded', () => {
    createIcons({icons});
});