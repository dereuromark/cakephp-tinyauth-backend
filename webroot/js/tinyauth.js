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

    // Dark-mode toggle (data-action="toggle-dark-mode" button)
    document.addEventListener('click', function(event) {
        var btn = event.target.closest('[data-action="toggle-dark-mode"]');
        if (!btn) {
            return;
        }
        var nowDark = !document.documentElement.classList.contains('dark');
        document.documentElement.classList.toggle('dark', nowDark);
        localStorage.setItem('darkMode', nowDark ? 'true' : 'false');
        document.querySelectorAll('[data-dark-mode-icon]').forEach(function(el) {
            el.hidden = el.dataset.darkModeIcon !== (nowDark ? 'dark' : 'light');
        });
    });

    // Sync dark-mode icon visibility on initial load (FOUC bootstrap set the class already)
    var initialDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-dark-mode-icon]').forEach(function(el) {
        el.hidden = el.dataset.darkModeIcon !== (initialDark ? 'dark' : 'light');
    });
});

// Generic dropdown menu: click [data-menu-toggle] toggles its scope's [data-menu].
// Click outside or Escape closes any open menu. Survives HTMX swaps via document delegation.
(function() {
    function closeAllMenus(except) {
        document.querySelectorAll('[data-menu].is-open').forEach(function(m) {
            if (m !== except) {
                m.classList.remove('is-open');
            }
        });
    }

    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('[data-menu-toggle]');
        if (toggle) {
            event.stopPropagation();
            var scope = toggle.closest('[data-menu-scope]') || toggle;
            var menu = scope.querySelector('[data-menu]');
            if (menu) {
                var willOpen = !menu.classList.contains('is-open');
                closeAllMenus(menu);
                menu.classList.toggle('is-open', willOpen);
            }
            return;
        }

        if (event.target.closest('[data-menu].is-open')) {
            return;
        }
        closeAllMenus(null);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAllMenus(null);
        }
    });
})();

// Role hierarchy drag-and-drop reorder (replaces Alpine @dragstart/@dragend/@drop bindings)
(function() {
    var draggingId = null;

    document.addEventListener('dragstart', function(event) {
        var item = event.target.closest('[data-role-id]');
        if (!item || !item.closest('[data-role-tree]')) {
            return;
        }
        draggingId = item.dataset.roleId;
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    });

    document.addEventListener('dragend', function() {
        draggingId = null;
    });

    document.addEventListener('dragover', function(event) {
        var item = event.target.closest('[data-role-id]');
        if (!item || !item.closest('[data-role-tree]')) {
            return;
        }
        event.preventDefault();
    });

    document.addEventListener('drop', function(event) {
        var item = event.target.closest('[data-role-id]');
        var tree = item && item.closest('[data-role-tree]');
        if (!item || !tree || draggingId === null) {
            return;
        }
        event.preventDefault();

        var targetId = item.dataset.roleId;
        if (targetId === draggingId) {
            return;
        }

        fetch(tree.dataset.reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': tree.dataset.csrfToken,
            },
            body: new URLSearchParams({
                role_id: draggingId,
                parent_id: targetId,
                sort_order: '0',
            }),
        }).then(function() {
            window.location.reload();
        });
    });
})();

// Namespace for TinyAuth functions
window.TinyAuth = window.TinyAuth || {};
