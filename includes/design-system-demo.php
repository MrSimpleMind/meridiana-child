<?php
/**
 * Design System Demo route for quick visual checks.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('template_include', function ($template) {
    if (empty($_GET['design-system-demo'])) {
        return $template;
    }

    if (!is_user_logged_in()) {
        return $template;
    }

    $demo_template = locate_template('templates/design-system-demo.php');

    return $demo_template ?: $template;
});
