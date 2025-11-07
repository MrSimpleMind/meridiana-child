<?php
/**
 * Header Template
 * Child Theme - Cooperativa La Meridiana
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Top Header Sticky - Visibile su tutte le pagine
if (is_user_logged_in()) {
    get_template_part('templates/parts/navigation/top-header');
}
?>
