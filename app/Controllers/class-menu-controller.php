<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Controller_Menu {
    public static function register() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_member_menu_font'));
        add_filter('wp_nav_menu_objects', array(__CLASS__, 'rewrite_protected_links'), 20, 2);
        add_filter('wp_nav_menu_items', array(__CLASS__, 'append_member_menu'), 20, 2);
        add_action('wp_head', array(__CLASS__, 'print_menu_styles'));
        add_action('wp_footer', array(__CLASS__, 'print_menu_recovery_script'), 99);
        add_action('wp_ajax_cloudari_bioenergy_member_status', array(__CLASS__, 'member_status'));
        add_action('wp_ajax_nopriv_cloudari_bioenergy_member_status', array(__CLASS__, 'member_status'));
    }

    public static function enqueue_member_menu_font() {
        wp_enqueue_style(
            'cloudari-bioenergy-varela',
            'https://fonts.googleapis.com/css2?family=Varela&display=swap',
            array(),
            CLOUDARI_BIOENERGY_ESSENTIALS_VERSION
        );
    }

    public static function rewrite_protected_links($items, $args) {
        $logged_in = Cloudari_BioEnergy_Model_Session_Repository::is_logged_in();

        foreach ($items as $item) {
            if (!isset($item->url) || !Cloudari_BioEnergy_Model_Protected_Page_Repository::url_is_protected($item->url)) {
                continue;
            }

            $item->url = $logged_in
                ? Cloudari_BioEnergy_Model_Session_Repository::private_url($item->url)
                : Cloudari_BioEnergy_Model_Session_Repository::login_url($item->url);
        }

        return $items;
    }

    public static function append_member_menu($items, $args) {
        if (!Cloudari_BioEnergy_Model_Session_Repository::is_logged_in()) {
            return $items;
        }

        if (!self::looks_like_members_menu($items)) {
            return $items;
        }

        $username = Cloudari_BioEnergy_Model_Member_Repository::current_username();
        if (!$username) {
            return $items;
        }

        $logout_url = wp_nonce_url(
            add_query_arg('cloudari_bioenergy_logout', '1', Cloudari_BioEnergy_Model_Session_Repository::login_url()),
            'cloudari_bioenergy_logout'
        );

        $label = sprintf('Welcome Back %s', $username);

        $items .= '<li class="menu-item menu-item-has-children cloudari-member-menu">';
        $items .= '<a href="' . esc_url(Cloudari_BioEnergy_Model_Session_Repository::private_url(Cloudari_BioEnergy_Model_Session_Repository::default_private_url())) . '" aria-haspopup="true" aria-expanded="false">' . esc_html($label) . '</a>';
        $items .= '<ul class="sub-menu">';
        $items .= '<li class="menu-item cloudari-member-menu-role"><span>' . esc_html(Cloudari_BioEnergy_Model_Member_Repository::ROLE_MEMBER) . '</span></li>';
        $items .= '<li class="menu-item"><a href="' . esc_url($logout_url) . '">Sign out</a></li>';
        $items .= '</ul>';
        $items .= '</li>';

        return $items;
    }

    public static function print_menu_styles() {
        ?>
        <style>
            .cloudari-member-menu { position: relative !important; }
            .cloudari-member-menu > a {
                color: #1c5986 !important;
                font-family: "Varela", Arial, sans-serif !important;
                font-weight: 400 !important;
                letter-spacing: 0 !important;
                text-transform: none !important;
            }
            .cloudari-member-menu .sub-menu {
                background: #ffffff !important;
                border: 1px solid #d8dee2 !important;
                box-shadow: 0 8px 22px rgba(47, 62, 79, 0.14) !important;
                margin: 0 !important;
                min-width: 210px !important;
                padding: 6px 0 !important;
                right: 0 !important;
                left: auto !important;
                z-index: 9999 !important;
            }
            .cloudari-member-menu .sub-menu li {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .cloudari-member-menu .sub-menu a,
            .cloudari-member-menu .sub-menu span {
                background: #ffffff !important;
                color: #2f3e4f !important;
                display: block !important;
                font-family: "Varela", Arial, sans-serif !important;
                font-weight: 400 !important;
                letter-spacing: 0 !important;
                line-height: 1.35 !important;
                padding: 10px 16px !important;
                text-decoration: none !important;
                text-transform: none !important;
            }
            .cloudari-member-menu .sub-menu a:hover,
            .cloudari-member-menu .sub-menu a:focus {
                background: #f5f8fa !important;
                color: #1c5986 !important;
            }
            .cloudari-member-menu:hover > .sub-menu,
            .cloudari-member-menu:focus-within > .sub-menu {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            .cloudari-member-menu-role span {
                color: #66727d !important;
                font-size: 12px !important;
            }
        </style>
        <?php
    }

    public static function print_menu_recovery_script() {
        $ajax_url = admin_url('admin-ajax.php');
        ?>
        <script>
            (function () {
                if (!window.fetch || !window.URL || !document.querySelectorAll) {
                    return;
                }

                var endpoint = <?php echo wp_json_encode(add_query_arg('action', 'cloudari_bioenergy_member_status', $ajax_url)); ?>;

                function toArray(list) {
                    return Array.prototype.slice.call(list || []);
                }

                function hasProtectedSlug(url, slugs) {
                    try {
                        var parsed = new URL(url, window.location.href);
                        var parts = parsed.pathname.split('/').filter(Boolean);
                        return parts.some(function (part) {
                            return slugs.indexOf(part.toLowerCase()) !== -1;
                        });
                    } catch (error) {
                        return false;
                    }
                }

                function withPrivateFlag(url) {
                    try {
                        var parsed = new URL(url, window.location.href);
                        parsed.searchParams.set('cloudari_private', '1');
                        return parsed.href;
                    } catch (error) {
                        return url;
                    }
                }

                function repairProtectedLinks(root, slugs) {
                    toArray(root.querySelectorAll('a[href]')).forEach(function (link) {
                        try {
                            var parsed = new URL(link.href, window.location.href);
                            var redirectTo = parsed.searchParams.get('redirect_to');
                            if (redirectTo && hasProtectedSlug(redirectTo, slugs)) {
                                link.href = withPrivateFlag(redirectTo);
                                return;
                            }

                            if (hasProtectedSlug(parsed.href, slugs)) {
                                link.href = withPrivateFlag(parsed.href);
                            }
                        } catch (error) {}
                    });
                }

                function menuLooksRelevant(menu, slugs) {
                    if (menu.closest && menu.closest('.sub-menu')) {
                        return false;
                    }

                    var text = (menu.textContent || '').toUpperCase();
                    if (text.indexOf('MEMBERS AREA') !== -1) {
                        return true;
                    }

                    return toArray(menu.querySelectorAll('a[href]')).some(function (link) {
                        return hasProtectedSlug(link.href, slugs);
                    });
                }

                function menuList(menu) {
                    if (menu.tagName && menu.tagName.toLowerCase() === 'ul') {
                        return menu;
                    }

                    return menu.querySelector ? menu.querySelector('ul') : null;
                }

                function findMemberMenu(list) {
                    var direct = toArray(list.children).filter(function (child) {
                        return child.classList && child.classList.contains('cloudari-member-menu');
                    });

                    return direct[0] || list.querySelector('.cloudari-member-menu');
                }

                function buildMemberMenu(data) {
                    var item = document.createElement('li');
                    item.className = 'menu-item menu-item-has-children cloudari-member-menu';

                    var link = document.createElement('a');
                    link.href = data.default_url;
                    link.setAttribute('aria-haspopup', 'true');
                    link.setAttribute('aria-expanded', 'false');
                    link.textContent = 'Welcome Back ' + data.username;

                    var submenu = document.createElement('ul');
                    submenu.className = 'sub-menu';

                    var role = document.createElement('li');
                    role.className = 'menu-item cloudari-member-menu-role';
                    var roleText = document.createElement('span');
                    roleText.textContent = data.role;
                    role.appendChild(roleText);

                    var signOut = document.createElement('li');
                    signOut.className = 'menu-item';
                    var signOutLink = document.createElement('a');
                    signOutLink.href = data.logout_url;
                    signOutLink.textContent = 'Sign out';
                    signOut.appendChild(signOutLink);

                    submenu.appendChild(role);
                    submenu.appendChild(signOut);
                    item.appendChild(link);
                    item.appendChild(submenu);

                    return item;
                }

                fetch(endpoint, { credentials: 'same-origin', cache: 'no-store' })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        var data = payload && payload.data ? payload.data : null;
                        if (!data) {
                            return;
                        }

                        if (data.logged_in && hasProtectedSlug(window.location.href, data.protected_slugs || [])) {
                            try {
                                var current = new URL(window.location.href);
                                if (current.searchParams.get('cloudari_private') !== '1') {
                                    window.location.replace(withPrivateFlag(current.href));
                                    return;
                                }
                            } catch (error) {}
                        }

                        var menus = toArray(document.querySelectorAll('.elementor-nav-menu, .menu, .nav-menu, nav ul'))
                            .filter(function (menu, index, all) {
                                return all.indexOf(menu) === index && menuLooksRelevant(menu, data.protected_slugs || []);
                            });

                        menus.forEach(function (menu) {
                            var list = menuList(menu);
                            if (!list) {
                                return;
                            }

                            if (!data.logged_in) {
                                toArray(list.querySelectorAll('.cloudari-member-menu')).forEach(function (item) {
                                    item.parentNode.removeChild(item);
                                });
                                return;
                            }

                            repairProtectedLinks(menu, data.protected_slugs || []);

                            var existing = findMemberMenu(list);
                            if (!existing) {
                                list.appendChild(buildMemberMenu(data));
                                return;
                            }

                            var link = existing.querySelector('a');
                            var role = existing.querySelector('.cloudari-member-menu-role span');
                            var signOut = existing.querySelector('.sub-menu a[href*="cloudari_bioenergy_logout"]');

                            if (link) {
                                link.href = data.default_url;
                                link.textContent = 'Welcome Back ' + data.username;
                            }
                            if (role) {
                                role.textContent = data.role;
                            }
                            if (signOut) {
                                signOut.href = data.logout_url;
                                signOut.textContent = 'Sign out';
                            }
                        });
                    })
                    .catch(function () {});
            }());
        </script>
        <?php
    }

    public static function member_status() {
        Cloudari_BioEnergy_Controller_Access::send_private_headers();

        if (!Cloudari_BioEnergy_Model_Session_Repository::is_logged_in()) {
            wp_send_json_success(
                array(
                    'logged_in' => false,
                    'protected_slugs' => Cloudari_BioEnergy_Model_Settings::protected_slugs(),
                )
            );
        }

        $logout_url = wp_nonce_url(
            add_query_arg('cloudari_bioenergy_logout', '1', Cloudari_BioEnergy_Model_Session_Repository::login_url()),
            'cloudari_bioenergy_logout'
        );

        wp_send_json_success(
            array(
                'logged_in' => true,
                'username' => Cloudari_BioEnergy_Model_Member_Repository::current_username(),
                'role' => Cloudari_BioEnergy_Model_Member_Repository::ROLE_MEMBER,
                'logout_url' => $logout_url,
                'default_url' => Cloudari_BioEnergy_Model_Session_Repository::private_url(Cloudari_BioEnergy_Model_Session_Repository::default_private_url()),
                'protected_slugs' => Cloudari_BioEnergy_Model_Settings::protected_slugs(),
            )
        );
    }

    private static function looks_like_members_menu($items) {
        if (false !== stripos((string) $items, 'MEMBERS AREA')) {
            return true;
        }

        foreach (Cloudari_BioEnergy_Model_Settings::protected_slugs() as $slug) {
            if ($slug && false !== stripos((string) $items, '/' . $slug . '/')) {
                return true;
            }
        }

        return false;
    }
}
