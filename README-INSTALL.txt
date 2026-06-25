Cloudari BioEnergy Essentials
=============================

Purpose
-------
This plugin protects the private EERA Bioenergy members-area pages imported with the Elementor website kit.

Access model
------------
The plugin does not use WordPress users, WordPress roles, or WordPress login for members-area access.

It creates its own isolated members list in plugin options:

- Passwords are stored with PHP password_hash using PASSWORD_BCRYPT.
- Login sessions use a random token in an HttpOnly cookie.
- Only a hashed session key is stored server-side as a temporary WordPress transient.
- Every private access account has the same plugin role: Member.
- WordPress users and WordPress roles do not grant access to the members area.
- The default login page is /members-login/ and contains the shortcode [cloudari_bioenergy_login].
- The login page title is hidden automatically for Elementor/Hello Theme and common WordPress title selectors.
- Protected members-area menu links are rewritten to the login page when no private session exists.
- After a private login, the main menu shows "Welcome Back {username}" with the Member role and a logout link.

Architecture
------------
The plugin follows a WordPress-friendly MVC-style structure:

- cloudari-bioenergy-essentials.php: bootstrap, constants and manual loader.
- app/Core/class-plugin.php: application composition and module registration.
- app/Core/class-installer.php: activation, upgrades and login page creation.
- app/Core/class-view.php: small PHP view renderer.
- app/Models/class-settings.php: option names, defaults and shared settings.
- app/Models/class-member-repository.php: plugin-only member storage and bcrypt password checks.
- app/Models/class-session-repository.php: private session token, cookie and login URL helpers.
- app/Models/class-protected-page-repository.php: protected page lookup.
- app/Controllers/class-auth-controller.php: login/logout request handling.
- app/Controllers/class-access-controller.php: page protection, REST cleanup, sitemap/search hiding.
- app/Controllers/class-admin-members-controller.php: back-office screen under Users > BioEnergy Access.
- app/Controllers/class-login-controller.php: frontend shortcode controller.
- app/Controllers/class-menu-controller.php: members-area menu link rewriting and signed-in dropdown.
- app/Views/admin/members-access.php: admin UI.
- app/Views/public/login-form.php: frontend login form.
- app/Views/public/signed-in.php: signed-in state.
- app/Views/public/partials/login-styles.php: login styles partial.

Protected slugs
---------------
- eera-bioenergy-templates-logo
- core-documents
- joint-programme-steering-committee-meetings
- management-board-meetings
- technology-watch
- collaborative-project-generation

Install order
-------------
1. Install and activate Elementor and Elementor Pro.
2. Install and activate Cloudari BioEnergy Essentials.
3. Import the Elementor website kit.
4. Go to WordPress Admin > Users > BioEnergy Access.
5. Create one or more plugin-only member usernames and passwords.

Cache note
----------
The plugin sends no-store/no-cache headers, varies private responses by Cookie, and defines DONOTCACHEPAGE for private sessions, login/logout requests, and protected pages. If the production site uses a server-level full-page cache or CDN, exclude /members-login/ and the protected slugs from public HTML caching, or vary cache by the cloudari_bioenergy_session cookie. Purge existing page/CDN cache after updating the plugin.

Security note
-------------
The plugin restricts access to the WordPress pages and removes protected page content from public REST responses and sitemaps. If you later upload private PDFs or ZIPs into the public /wp-content/uploads/ folder, direct file URLs can still be reachable by the web server. For truly private documents, store them outside public uploads or route downloads through a protected PHP endpoint.
