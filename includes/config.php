<?php
/**
 * Local configuration — loaded from .env (never commit secrets).
 */
require_once __DIR__ . '/bootstrap.php';
habitflow_load_env();

if (!defined('QWEN_API_KEY')) {
    define('QWEN_API_KEY', getenv('QWEN_API_KEY') ?: '');
}
