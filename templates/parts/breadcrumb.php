<?php
/**
 * Template Part: Breadcrumb Navigation
 * 
 * Visualizza il breadcrumb intelligente con torna indietro
 * Richiama le funzioni da includes/breadcrumb-navigation.php
 * 
 * @package Meridiana Child
 */

if (!defined('ABSPATH')) exit;

// Render back button
meridiana_render_back_button();

// Render breadcrumb se non siamo in home
if (!is_front_page()) {
    meridiana_render_breadcrumb();
}
