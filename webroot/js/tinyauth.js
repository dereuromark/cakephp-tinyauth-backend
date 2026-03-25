// plugins/TinyAuthBackend/webroot/js/tinyauth.js

document.addEventListener('DOMContentLoaded', function() {
    // HTMX event handlers
    document.body.addEventListener('htmx:afterSwap', function(event) {
        // Handle search results visibility
        if (event.target.id === 'search-results') {
            event.target.classList.toggle('hidden', event.target.innerHTML.trim() === '');
        }
    });

    // Close search results on click outside
    document.addEventListener('click', function(event) {
        const searchResults = document.getElementById('search-results');
        const searchInput = document.querySelector('input[name="q"]');
        if (searchResults && !searchResults.contains(event.target) && event.target !== searchInput) {
            searchResults.classList.add('hidden');
        }
    });

    // Keyboard shortcut: / to focus search
    document.addEventListener('keydown', function(event) {
        if (event.key === '/' && document.activeElement.tagName !== 'INPUT') {
            event.preventDefault();
            document.querySelector('input[name="q"]')?.focus();
        }
    });
});

// Namespace for TinyAuth functions
window.TinyAuth = window.TinyAuth || {};

// Matrix cell toggle
window.TinyAuth.togglePermission = function(actionId, roleId, currentState) {
    const states = ['none', 'allow', 'deny'];
    const nextIndex = (states.indexOf(currentState) + 1) % states.length;
    const nextState = states[nextIndex];

    htmx.ajax('POST', window.TinyAuth.urls.aclToggle, {
        values: {
            action_id: actionId,
            role_id: roleId,
            type: nextState
        },
        target: '#cell-' + actionId + '-' + roleId,
        swap: 'outerHTML'
    });
};

