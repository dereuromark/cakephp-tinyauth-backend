<?php
/**
 * @var \Cake\View\View $this
 * @var array $stats
 * @var array $features
 * @var array $recentControllers
 */
$this->assign('title', 'Dashboard');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">TinyAuth Dashboard</h1>
            <p class="text-gray-500 dark:text-gray-400">Authorization management at a glance</p>
        </div>
        <a href="<?= $this->Url->build(['controller' => 'Sync', 'action' => 'controllers']) ?>"
           class="btn btn-primary">
            Sync Controllers
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-3xl font-bold text-primary"><?= $stats['controllers'] ?></div>
            <div class="text-sm text-gray-500">Controllers</div>
        </div>
        <div class="card p-4">
            <div class="text-3xl font-bold text-primary"><?= $stats['actions'] ?></div>
            <div class="text-sm text-gray-500">Actions</div>
        </div>
        <div class="card p-4">
            <div class="text-3xl font-bold text-green-500"><?= $stats['public_actions'] ?></div>
            <div class="text-sm text-gray-500">Public Actions</div>
        </div>
        <div class="card p-4">
            <div class="text-3xl font-bold text-primary"><?= $stats['roles'] ?></div>
            <div class="text-sm text-gray-500">Roles</div>
        </div>
    </div>

    <!-- Features & Quick Links -->
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Enabled Features -->
        <div class="card">
            <div class="p-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="font-semibold">Enabled Features</h2>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="<?= $features['allow'] ? 'text-green-500' : 'text-gray-400' ?>">
                            <?= $features['allow'] ? '&#10004;' : '&#10006;' ?>
                        </span>
                        <span>Allow (Public Actions)</span>
                    </div>
                    <?php if ($features['allow']) { ?>
                    <a href="<?= $this->Url->build(['controller' => 'Allow', 'action' => 'index']) ?>"
                       class="text-primary text-sm">Manage &rarr;</a>
                    <?php } ?>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="<?= $features['acl'] ? 'text-green-500' : 'text-gray-400' ?>">
                            <?= $features['acl'] ? '&#10004;' : '&#10006;' ?>
                        </span>
                        <span>ACL Permissions</span>
                    </div>
                    <?php if ($features['acl']) { ?>
                    <a href="<?= $this->Url->build(['controller' => 'Acl', 'action' => 'index']) ?>"
                       class="text-primary text-sm">Manage &rarr;</a>
                    <?php } ?>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="<?= $features['resources'] ? 'text-green-500' : 'text-gray-400' ?>">
                            <?= $features['resources'] ? '&#10004;' : '&#10006;' ?>
                        </span>
                        <span>Resource Permissions</span>
                    </div>
                    <?php if ($features['resources']) { ?>
                    <a href="<?= $this->Url->build(['controller' => 'Resources', 'action' => 'index']) ?>"
                       class="text-primary text-sm">Manage &rarr;</a>
                    <?php } ?>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="<?= $features['scopes'] ? 'text-green-500' : 'text-gray-400' ?>">
                            <?= $features['scopes'] ? '&#10004;' : '&#10006;' ?>
                        </span>
                        <span>Permission Scopes</span>
                    </div>
                    <?php if ($features['scopes']) { ?>
                    <a href="<?= $this->Url->build(['controller' => 'Scopes', 'action' => 'index']) ?>"
                       class="text-primary text-sm">Manage &rarr;</a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="p-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="font-semibold">Quick Actions</h2>
            </div>
            <div class="p-4 space-y-2">
                <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'concepts']) ?>"
                   class="block p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800">
                    <div class="font-medium text-blue-700 dark:text-blue-300">Learn Concepts</div>
                    <div class="text-sm text-blue-600 dark:text-blue-400">Understand how TinyAuth works</div>
                </a>
                <a href="<?= $this->Url->build(['controller' => 'Sync', 'action' => 'controllers']) ?>"
                   class="block p-3 rounded-lg bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                    <div class="font-medium">Sync Controllers</div>
                    <div class="text-sm text-gray-500">Discover new controllers and actions</div>
                </a>
                <?php if ($features['resources']) { ?>
                <a href="<?= $this->Url->build(['controller' => 'Sync', 'action' => 'resources']) ?>"
                   class="block p-3 rounded-lg bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                    <div class="font-medium">Sync Resources</div>
                    <div class="text-sm text-gray-500">Discover entity resources</div>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Additional Stats (if features enabled) -->
    <?php if ($features['resources'] || $features['scopes']) { ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php if ($features['resources']) { ?>
        <div class="card p-4">
            <div class="text-3xl font-bold text-purple-500"><?= $stats['resources'] ?? 0 ?></div>
            <div class="text-sm text-gray-500">Resources</div>
        </div>
        <div class="card p-4">
            <div class="text-3xl font-bold text-purple-500"><?= $stats['abilities'] ?? 0 ?></div>
            <div class="text-sm text-gray-500">Abilities</div>
        </div>
        <?php } ?>
        <?php if ($features['scopes']) { ?>
        <div class="card p-4">
            <div class="text-3xl font-bold text-orange-500"><?= $stats['scopes'] ?? 0 ?></div>
            <div class="text-sm text-gray-500">Scopes</div>
        </div>
        <?php } ?>
        <div class="card p-4">
            <div class="text-3xl font-bold text-blue-500"><?= $stats['acl_permissions'] ?></div>
            <div class="text-sm text-gray-500">ACL Rules</div>
        </div>
    </div>
    <?php } ?>

    <!-- Recent Controllers -->
    <?php if ($recentControllers) { ?>
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <h2 class="font-semibold">Recently Updated Controllers</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-slate-700">
            <?php foreach ($recentControllers as $controller) { ?>
            <a href="<?= $this->Url->build(['controller' => 'Acl', 'action' => 'index', '?' => ['controller_id' => $controller->id]]) ?>"
               class="block p-4 hover:bg-gray-50 dark:hover:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium"><?= h($controller->full_path) ?></span>
                    </div>
                    <span class="text-sm text-gray-500">
                        <?= $controller->modified ? $controller->modified->timeAgoInWords() : 'N/A' ?>
                    </span>
                </div>
            </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
