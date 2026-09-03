<?php
/**
 * CONFIGURACIÓN DE PHPMAILER - EcoRuta
 * =====================================
 * Este archivo centraliza la configuración de envío de correos.
 */

// ========== CONFIGURACIÓN SMTP ==========
if (!defined("MAIL_HOST")) define("MAIL_HOST", "smtp.gmail.com");
if (!defined("MAIL_USERNAME")) define("MAIL_USERNAME", "alvaroortega914@gmail.com");
if (!defined("MAIL_PASSWORD")) define("MAIL_PASSWORD", "bxjmhjpapdmvxlri");
if (!defined("MAIL_SECURE")) define("MAIL_SECURE", "tls"); // 'tls' o 'ssl'
if (!defined("MAIL_PORT")) define("MAIL_PORT", 587);

// ========== CONFIGURACIÓN DEL REMITENTE ==========
if (!defined("MAIL_FROM_ADDRESS")) define("MAIL_FROM_ADDRESS", "alvaroortega914@gmail.com");
if (!defined("MAIL_FROM_NAME")) define("MAIL_FROM_NAME", "EcoRuta - Logística Verde");

// ========== CONFIGURACIÓN GENERAL ==========
if (!defined("URL_SISTEMA")) {
    $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define("URL_SISTEMA", $protocolo . $host . "/EcoRuta/");
}
if (!defined("DEBUG_MAIL")) define("DEBUG_MAIL", false);
?>