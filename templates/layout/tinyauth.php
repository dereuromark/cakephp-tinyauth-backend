<?php
// plugins/TinyAuthBackend/templates/layout/tinyauth.php
declare(strict_types=1);

/**
 * @var \Cake\View\View $this
 */
$this->loadHelper('TinyAuthBackend.TinyAuth');
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken')) ?>
    <title><?= $this->fetch('title') ?> - TinyAuth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script>
        // Configure HTMX to include CSRF token in all requests
        document.addEventListener('htmx:configRequest', function(event) {
            var csrfToken = document.querySelector('meta[name="csrfToken"]');
            if (csrfToken) {
                event.detail.headers['X-CSRF-Token'] = csrfToken.content;
            }
        });
    </script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                    }
                }
            }
        }
    </script>
    <script>
        window.TinyAuth = {
            urls: {
                aclToggle: <?= json_encode($this->Url->build(['plugin' => 'TinyAuthBackend', 'controller' => 'Acl', 'action' => 'toggle', 'prefix' => 'Admin'])) ?>,
                resourceToggle: <?= json_encode($this->Url->build(['plugin' => 'TinyAuthBackend', 'controller' => 'Resources', 'action' => 'toggle', 'prefix' => 'Admin'])) ?>
            }
        };
    </script>
    <?= $this->Html->css('TinyAuthBackend.tinyauth') ?>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    <a href="<?= $this->Url->build(['plugin' => 'TinyAuthBackend', 'controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Admin']) ?>"
                       class="font-semibold text-lg">
                        TinyAuth
                    </a>
                </div>

                <!-- Search -->
                <div class="flex-1 max-w-md mx-8 relative">
                    <input type="search"
                           placeholder="Search controllers, actions, roles..."
                           class="w-full px-3 py-1.5 text-sm bg-gray-100 dark:bg-slate-700 border-0 rounded-md focus:ring-2 focus:ring-primary"
                           hx-get="<?= $this->Url->build(['plugin' => 'TinyAuthBackend', 'prefix' => 'Admin', 'controller' => 'Acl', 'action' => 'search']) ?>"
                           hx-trigger="keyup changed delay:300ms"
                           hx-target="#search-results"
                           name="q">
                    <div id="search-results" class="absolute mt-1 bg-white dark:bg-slate-800 rounded-md shadow-lg hidden"></div>
                </div>

                <!-- Navigation (feature-aware) -->
                <nav class="flex items-center gap-1">
                    <?php
					$navItems = $this->TinyAuth->getNavigationItems();
					$currentController = $this->request->getParam('controller');
					foreach ($navItems as $item) {
						$isActive = $currentController === $item['route']['controller'];
						$route = $item['route'] + ['plugin' => 'TinyAuthBackend', 'prefix' => 'Admin'];
						?>
                    <a href="<?= $this->Url->build($route) ?>"
                       class="px-3 py-1.5 text-sm rounded-md <?= $isActive ? 'bg-primary text-white' : 'hover:bg-gray-100 dark:hover:bg-slate-700' ?>">
                        <?= h($item['label']) ?>
                    </a>
                    <?php } ?>

                    <!-- Theme toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                            class="ml-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700"
                            :aria-label="darkMode ? '<?= __('Switch to light mode') ?>' : '<?= __('Switch to dark mode') ?>'"
                            type="button">
                        <span x-show="!darkMode">🌙</span>
                        <span x-show="darkMode">☀️</span>
                    </button>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <?= $this->Flash->render() ?>
    </div>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?= $this->fetch('content') ?>
    </main>

    <?= $this->Html->script('TinyAuthBackend.tinyauth') ?>
</body>
</html>
