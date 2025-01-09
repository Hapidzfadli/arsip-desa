import { ref } from 'vue';

export function useSidebar() {
    const isOpen = ref(false);
    const isCollapsed = ref(false);

    const toggle = () => {
        isOpen.value = !isOpen.value;
    };

    const close = () => {
        isOpen.value = false;
    };

    const toggleCollapse = () => {
        isCollapsed.value = !isCollapsed.value;
        // Store the preference in localStorage for persistence
        localStorage.setItem('sidebarCollapsed', isCollapsed.value);
    };

    // Initialize collapsed state from localStorage
    const initializeCollapsedState = () => {
        const stored = localStorage.getItem('sidebarCollapsed');
        if (stored !== null) {
            isCollapsed.value = stored === 'true';
        }
    };

    // Call this immediately
    initializeCollapsedState();

    return {
        isOpen,
        isCollapsed,
        toggle,
        close,
        toggleCollapse
    };
}