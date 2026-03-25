<?php
/**
 * @var \Cake\View\View $this
 * @var array $features
 */
$this->assign('title', 'Concepts');
?>

<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold">TinyAuth Concepts</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Understanding the authorization flow and how each feature works together</p>
    </div>

    <!-- Overview Flow Diagram -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold mb-4">Authorization Flow</h2>
        <div class="flex flex-wrap items-center justify-center gap-2 text-sm">
            <div class="px-4 py-2 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg font-medium">
                1. Allow
            </div>
            <span class="text-gray-400">&rarr;</span>
            <div class="px-4 py-2 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-lg font-medium">
                2. ACL
            </div>
            <span class="text-gray-400">&rarr;</span>
            <div class="px-4 py-2 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-lg font-medium">
                3. Resources
            </div>
            <span class="text-gray-400">+</span>
            <div class="px-4 py-2 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-lg font-medium">
                Scopes
            </div>
        </div>
        <p class="text-center text-gray-500 dark:text-gray-400 mt-4 text-sm">
            Each layer adds more granular control over what users can access
        </p>
    </div>

    <!-- Allow Section -->
    <div class="card overflow-hidden">
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border-b border-green-200 dark:border-green-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center font-bold">1</div>
                <div>
                    <h2 class="text-lg font-semibold">Allow (Public Actions)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">First gate: Is this action public?</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="prose dark:prose-invert max-w-none">
                <p><strong>Purpose:</strong> Define which controller actions are publicly accessible without any authentication.</p>

                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-lg">
                    <p class="font-medium mb-2">Examples:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li><code>Pages/display</code> - Public homepage</li>
                        <li><code>Users/login</code> - Login page (must be public!)</li>
                        <li><code>Users/register</code> - Registration page</li>
                        <li><code>Api/status</code> - Health check endpoint</li>
                    </ul>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                    <strong>Flow:</strong> If an action is marked as "Allow" (public), no further authentication or authorization checks are performed.
                    The user can access it whether logged in or not.
                </p>
            </div>
            <?php if ($features['allow']) { ?>
            <a href="<?= $this->Url->build(['controller' => 'Allow', 'action' => 'index']) ?>"
               class="inline-flex items-center gap-2 text-green-600 dark:text-green-400 text-sm font-medium">
                Manage Public Actions &rarr;
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- ACL Section -->
    <div class="card overflow-hidden">
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">2</div>
                <div>
                    <h2 class="text-lg font-semibold">ACL (Access Control List)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Second gate: Does the user's role allow this action?</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="prose dark:prose-invert max-w-none">
                <p><strong>Purpose:</strong> Control which <em>roles</em> can access which controller actions. This is role-based access control (RBAC).</p>

                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-lg">
                    <p class="font-medium mb-2">Example Permission Matrix:</p>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-slate-700">
                                <th class="text-left py-2">Action</th>
                                <th class="text-center py-2">User</th>
                                <th class="text-center py-2">Moderator</th>
                                <th class="text-center py-2">Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b dark:border-slate-700">
                                <td class="py-2">Dashboard/index</td>
                                <td class="text-center text-green-500">&#10004;</td>
                                <td class="text-center text-green-500">&#10004;</td>
                                <td class="text-center text-green-500">&#10004;</td>
                            </tr>
                            <tr class="border-b dark:border-slate-700">
                                <td class="py-2">Reports/index</td>
                                <td class="text-center text-red-500">&#10006;</td>
                                <td class="text-center text-green-500">&#10004;</td>
                                <td class="text-center text-green-500">&#10004;</td>
                            </tr>
                            <tr>
                                <td class="py-2">Admin/Users/delete</td>
                                <td class="text-center text-red-500">&#10006;</td>
                                <td class="text-center text-red-500">&#10006;</td>
                                <td class="text-center text-green-500">&#10004;</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                    <strong>Flow:</strong> If the action is not public, TinyAuth checks if the user is logged in and if their role has permission for that action.
                    Roles can inherit permissions from parent roles.
                </p>
            </div>
            <?php if ($features['acl']) { ?>
            <a href="<?= $this->Url->build(['controller' => 'Acl', 'action' => 'index']) ?>"
               class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 text-sm font-medium">
                Manage ACL Permissions &rarr;
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- Resources Section -->
    <div class="card overflow-hidden">
        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-200 dark:border-purple-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold">3</div>
                <div>
                    <h2 class="text-lg font-semibold">Resources (Entity-Level Permissions)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Third gate: Can the user perform this action on THIS specific entity?</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="prose dark:prose-invert max-w-none">
                <p><strong>Purpose:</strong> Control access to individual database records (entities). Goes beyond "can user access Articles?" to "can user edit <em>this specific</em> Article?"</p>

                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-lg">
                    <p class="font-medium mb-2">Example - Article Resource:</p>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-slate-700">
                                <th class="text-left py-2">Ability</th>
                                <th class="text-center py-2">User</th>
                                <th class="text-center py-2">Moderator</th>
                                <th class="text-center py-2">Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b dark:border-slate-700">
                                <td class="py-2">view</td>
                                <td class="text-center">All</td>
                                <td class="text-center">All</td>
                                <td class="text-center">All</td>
                            </tr>
                            <tr class="border-b dark:border-slate-700">
                                <td class="py-2">edit</td>
                                <td class="text-center text-orange-500">Own only</td>
                                <td class="text-center">All</td>
                                <td class="text-center">All</td>
                            </tr>
                            <tr>
                                <td class="py-2">delete</td>
                                <td class="text-center text-orange-500">Own only</td>
                                <td class="text-center text-orange-500">Own only</td>
                                <td class="text-center">All</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                    <strong>Key Concepts:</strong>
                </p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 list-disc list-inside">
                    <li><strong>Resource:</strong> A type of entity (Article, Project, Comment)</li>
                    <li><strong>Ability:</strong> An action on that resource (view, edit, delete, publish)</li>
                    <li><strong>Scope:</strong> A condition that limits access (see below)</li>
                </ul>
            </div>
            <?php if ($features['resources']) { ?>
            <a href="<?= $this->Url->build(['controller' => 'Resources', 'action' => 'index']) ?>"
               class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 text-sm font-medium">
                Manage Resources &rarr;
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- Scopes Section -->
    <div class="card overflow-hidden">
        <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">+</div>
                <div>
                    <h2 class="text-lg font-semibold">Scopes (Permission Conditions)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Modifiers that narrow down resource access</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="prose dark:prose-invert max-w-none">
                <p><strong>Purpose:</strong> Define reusable conditions that limit which records a user can access. Scopes make resource permissions dynamic based on relationships.</p>

                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-lg space-y-4">
                    <div>
                        <p class="font-medium">Common Scope Types:</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div class="p-3 bg-white dark:bg-slate-700 rounded border dark:border-slate-600">
                            <p class="font-medium text-orange-600 dark:text-orange-400">own</p>
                            <p class="text-gray-600 dark:text-gray-400">User can only access records they created</p>
                            <code class="text-xs">user_id = current_user_id</code>
                        </div>
                        <div class="p-3 bg-white dark:bg-slate-700 rounded border dark:border-slate-600">
                            <p class="font-medium text-orange-600 dark:text-orange-400">team</p>
                            <p class="text-gray-600 dark:text-gray-400">User can access records from their team</p>
                            <code class="text-xs">team_id = current_user_team_id</code>
                        </div>
                        <div class="p-3 bg-white dark:bg-slate-700 rounded border dark:border-slate-600">
                            <p class="font-medium text-orange-600 dark:text-orange-400">department</p>
                            <p class="text-gray-600 dark:text-gray-400">User can access records from their department</p>
                            <code class="text-xs">department_id = current_user_dept_id</code>
                        </div>
                        <div class="p-3 bg-white dark:bg-slate-700 rounded border dark:border-slate-600">
                            <p class="font-medium text-orange-600 dark:text-orange-400">company</p>
                            <p class="text-gray-600 dark:text-gray-400">User can access records from their company (multi-tenant)</p>
                            <code class="text-xs">company_id = current_user_company_id</code>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                    <strong>How it works:</strong> When a scope is applied to a resource ability, TinyAuth automatically adds WHERE conditions to queries
                    or checks ownership when accessing individual records.
                </p>
            </div>
            <?php if ($features['scopes']) { ?>
            <a href="<?= $this->Url->build(['controller' => 'Scopes', 'action' => 'index']) ?>"
               class="inline-flex items-center gap-2 text-orange-600 dark:text-orange-400 text-sm font-medium">
                Manage Scopes &rarr;
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- Roles Section -->
    <div class="card overflow-hidden">
        <div class="p-4 bg-gray-100 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gray-500 text-white flex items-center justify-center">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Roles (User Groups)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">The foundation: grouping users by their access level</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="prose dark:prose-invert max-w-none">
                <p><strong>Purpose:</strong> Define user groups with hierarchical inheritance. Higher roles automatically inherit permissions from lower roles.</p>

                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-lg">
                    <p class="font-medium mb-3">Example Hierarchy:</p>
                    <div class="flex items-center justify-center gap-4 text-sm">
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-slate-700 flex items-center justify-center mb-2">
                                <span class="text-2xl">&#128100;</span>
                            </div>
                            <p class="font-medium">User</p>
                            <p class="text-xs text-gray-500">Base level</p>
                        </div>
                        <span class="text-gray-400 text-2xl">&rarr;</span>
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center mb-2">
                                <span class="text-2xl">&#128101;</span>
                            </div>
                            <p class="font-medium">Moderator</p>
                            <p class="text-xs text-gray-500">+ User perms</p>
                        </div>
                        <span class="text-gray-400 text-2xl">&rarr;</span>
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center mb-2">
                                <span class="text-2xl">&#128081;</span>
                            </div>
                            <p class="font-medium">Admin</p>
                            <p class="text-xs text-gray-500">Full access</p>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                    <strong>Inheritance:</strong> Admin inherits all Moderator permissions, Moderator inherits all User permissions.
                    You only need to grant permissions at the lowest required level.
                </p>
            </div>
            <?php if ($features['roles']) { ?>
            <a href="<?= $this->Url->build(['controller' => 'Roles', 'action' => 'index']) ?>"
               class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 text-sm font-medium">
                Manage Roles &rarr;
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- Summary -->
    <div class="card p-6 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20">
        <h2 class="text-lg font-semibold mb-3">Putting It All Together</h2>
        <div class="prose dark:prose-invert max-w-none text-sm">
            <p>When a user tries to access something in your application:</p>
            <ol class="space-y-2">
                <li><strong>Allow Check:</strong> Is this a public action? If yes, grant access immediately.</li>
                <li><strong>Authentication:</strong> Is the user logged in? If not, redirect to login.</li>
                <li><strong>ACL Check:</strong> Does the user's role have permission for this controller/action?</li>
                <li><strong>Resource Check:</strong> If accessing a specific entity, can this user perform this ability on this record?</li>
                <li><strong>Scope Application:</strong> If a scope is defined, filter the records or check ownership.</li>
            </ol>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                This layered approach gives you flexible, fine-grained control over your application's security.
            </p>
        </div>
    </div>

    <!-- Back to Dashboard -->
    <div class="flex justify-center">
        <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'index']) ?>"
           class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>
    </div>
</div>
