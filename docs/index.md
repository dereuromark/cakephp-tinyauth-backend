---
layout: home

hero:
  name: cakephp-tinyauth-backend
  text: Database-backed admin UI for TinyAuth
  tagline: Manage CakePHP roles, public actions, controller/action ACL, resource permissions, and scopes from a self-contained admin backend — no INI files required.
  image:
    src: /logo.svg
    alt: cakephp-tinyauth-backend
  actions:
    - theme: brand
      text: Get Started
      link: /guide/
    - theme: alt
      text: Strategies
      link: /strategies/
    - theme: alt
      text: Authorization
      link: /authorization/
    - theme: alt
      text: View on GitHub
      link: https://github.com/dereuromark/cakephp-tinyauth-backend

features:
  - icon: 🗃️
    title: DB Instead of INI
    details: Move classic TinyAuth allow and acl rules out of INI files and into the database via drop-in adapters — same runtime behavior, editable from a UI.
  - icon: 🧭
    title: Admin UI
    details: A self-contained Bootstrap-free, Tailwind-styled backend at /admin/auth for the ACL matrix, public actions, roles, resources, and scopes.
  - icon: 🪜
    title: Four Usage Strategies
    details: Climb a ladder from adapter-only DB storage to a full backend with role hierarchy, resources, scopes, and CakePHP Authorization integration.
  - icon: 🛡️
    title: Fails Closed
    details: The admin UI rejects every request with 403 until you configure an explicit access gate — regardless of debug mode. No accidental exposure.
  - icon: 🧩
    title: Authorization Integration
    details: Ship TinyAuthPolicy and TinyAuthResolver to drive entity-level checks through cakephp/authorization without per-resource policy wrappers.
  - icon: 🔗
    title: External Role Sources
    details: Drive role aliases from app config, a callback, a JWT claim, LDAP, or SSO while keeping ACL and resource assignments in the backend.
---
