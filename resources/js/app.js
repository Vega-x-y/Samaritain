import Alpine from "alpinejs";
import collapse from "@alpinejs/collapse";
import "./notificationDropdown";
import { createIcons, icons } from "lucide";

// Theme toggle: read preference, apply class, persist on changes
(function () {
    function applyTheme(theme) {
        if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }
    }

    // initialize from localStorage or OS preference
    var stored = null;
    try {
        stored = localStorage.getItem("theme");
    } catch (e) {
        /* noop */
    }
    if (stored) {
        applyTheme(stored);
    } else if (
        window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
        applyTheme("dark");
    }

    // delegate click on #theme-toggle
    document.addEventListener("click", function (e) {
        var btn = e.target.closest && e.target.closest("#theme-toggle");
        if (!btn) return;
        var isDark = document.documentElement.classList.toggle("dark");
        try {
            localStorage.setItem("theme", isDark ? "dark" : "light");
        } catch (e) {
            /* noop */
        }
    });
})();

Alpine.plugin(collapse);

// Make Alpine globally accessible
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// Initialize Lucide icons
// Since Vite loads modules with type="module" (deferred), the DOM is already
// loaded by the time this code runs, so we can call createIcons() directly.
createIcons({ icons });

// Re-render Lucide icons after Livewire navigation
document.addEventListener("livewire:navigated", () => {
    createIcons({ icons });
});

// Re-render Lucide icons after Livewire loads
document.addEventListener("livewire:load", () => {
    createIcons({ icons });
});

// Bascule de thème manuelle (au cas où Alpine n'est pas encore prêt)
document.addEventListener("DOMContentLoaded", () => {
    createIcons({ icons });
});