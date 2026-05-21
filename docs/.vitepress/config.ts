import { defineConfig } from 'vitepress'

function sidebar() {
  return [
    {
      text: 'Guide',
      items: [
        { text: 'Overview', link: '/guide/' },
        { text: 'Installation', link: '/guide/installation' },
        { text: 'Admin Access', link: '/guide/admin-access' },
        { text: 'Feature Flags', link: '/guide/feature-flags' },
        { text: 'Strict CSP', link: '/guide/strict-csp' },
      ],
    },
    {
      text: 'Strategies',
      items: [
        { text: 'Overview', link: '/strategies/' },
        { text: 'Adapter-Only', link: '/strategies/adapter-only' },
        { text: 'Full Backend', link: '/strategies/full-backend' },
        { text: 'Native CakePHP Auth', link: '/strategies/native-auth' },
        { text: 'External Role Source', link: '/strategies/external-roles' },
      ],
    },
    {
      text: 'Permissions',
      items: [
        { text: 'Roles', link: '/permissions/roles' },
        { text: 'Allow (Public Actions)', link: '/permissions/allow' },
        { text: 'ACL Matrix', link: '/permissions/acl' },
        { text: 'Resources', link: '/permissions/resources' },
        { text: 'Scopes', link: '/permissions/scopes' },
      ],
    },
    {
      text: 'Authorization',
      items: [
        { text: 'Public Actions', link: '/authorization/authentication' },
        { text: 'Authorization Integration', link: '/authorization/' },
      ],
    },
    {
      text: 'Reference',
      items: [
        { text: 'Services API', link: '/reference/services' },
        { text: 'Frontend Assets', link: '/reference/assets' },
      ],
    },
  ]
}

export default defineConfig({
  title: 'cakephp-tinyauth-backend',
  description: 'A database-backed admin UI for managing CakePHP TinyAuth roles, permissions, and ACL — no INI files required.',
  base: '/cakephp-tinyauth-backend/',
  lastUpdated: true,
  cleanUrls: true,
  sitemap: {
    hostname: 'https://dereuromark.github.io/cakephp-tinyauth-backend/',
  },
  head: [
    ['link', { rel: 'icon', href: '/cakephp-tinyauth-backend/favicon.svg', type: 'image/svg+xml' }],
  ],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Guide', link: '/guide/', activeMatch: '/guide/' },
      { text: 'Strategies', link: '/strategies/', activeMatch: '/strategies/' },
      { text: 'Permissions', link: '/permissions/roles', activeMatch: '/permissions/' },
      { text: 'Authorization', link: '/authorization/', activeMatch: '/authorization/' },
      { text: 'Reference', link: '/reference/services', activeMatch: '/reference/' },
      {
        text: 'Links',
        items: [
          { text: 'GitHub', link: 'https://github.com/dereuromark/cakephp-tinyauth-backend' },
          { text: 'Packagist', link: 'https://packagist.org/packages/dereuromark/cakephp-tinyauth-backend' },
          { text: 'Issues', link: 'https://github.com/dereuromark/cakephp-tinyauth-backend/issues' },
        ],
      },
    ],
    sidebar: {
      '/guide/': sidebar(),
      '/strategies/': sidebar(),
      '/permissions/': sidebar(),
      '/authorization/': sidebar(),
      '/reference/': sidebar(),
    },
    socialLinks: [
      { icon: 'github', link: 'https://github.com/dereuromark/cakephp-tinyauth-backend' },
    ],
    search: {
      provider: 'local',
    },
    editLink: {
      pattern: 'https://github.com/dereuromark/cakephp-tinyauth-backend/edit/master/docs/:path',
      text: 'Edit this page on GitHub',
    },
    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright Mark Scherer',
    },
  },
})
