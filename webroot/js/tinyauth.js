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

// Matrix cell toggle
function togglePermission(actionId, roleId, currentState) {
    const states = ['none', 'allow', 'deny'];
    const nextIndex = (states.indexOf(currentState) + 1) % states.length;
    const nextState = states[nextIndex];

    htmx.ajax('POST', '/admin/tinyauth/acl/toggle', {
        values: {
            action_id: actionId,
            role_id: roleId,
            type: nextState
        },
        target: `#cell-${actionId}-${roleId}`,
        swap: 'outerHTML'
    });
}

// Resource permission toggle (with scope)
function toggleResourcePermission(abilityId, roleId, currentState, scopeId) {
    htmx.ajax('POST', '/admin/tinyauth/resources/toggle', {
        values: {
            ability_id: abilityId,
            role_id: roleId,
            type: currentState === 'none' ? 'allow' : (currentState === 'allow' ? 'deny' : 'none'),
            scope_id: scopeId || ''
        },
        target: `#rcell-${abilityId}-${roleId}`,
        swap: 'outerHTML'
    });
}
