<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Feature Flags Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file manages feature flags for safe rollbacks
    | during Laravel framework upgrades and new feature deployments.
    |
    */

    // Laravel Framework Upgrade Features
    'laravel_upgrade_new_syntax' => env('FEATURE_LARAVEL_UPGRADE_NEW_SYNTAX', false),
    'laravel_upgrade_new_middleware' => env('FEATURE_LARAVEL_UPGRADE_NEW_MIDDLEWARE', false),
    'laravel_upgrade_new_validation' => env('FEATURE_LARAVEL_UPGRADE_NEW_VALIDATION', false),
    'laravel_upgrade_new_database_features' => env('FEATURE_LARAVEL_UPGRADE_NEW_DATABASE_FEATURES', false),

    // Migration Safety Features
    'safe_migration_mode' => env('FEATURE_SAFE_MIGRATION_MODE', true),
    'migration_rollback_protection' => env('FEATURE_MIGRATION_ROLLBACK_PROTECTION', true),
    'database_backup_before_migration' => env('FEATURE_DATABASE_BACKUP_BEFORE_MIGRATION', true),

    // Application Features
    'new_answer_features' => env('FEATURE_NEW_ANSWER_FEATURES', false),
    'enhanced_question_search' => env('FEATURE_ENHANCED_QUESTION_SEARCH', false),
    'user_profile_enhancements' => env('FEATURE_USER_PROFILE_ENHANCEMENTS', false),
    'admin_dashboard_v2' => env('FEATURE_ADMIN_DASHBOARD_V2', false),

    // Performance Features
    'query_optimization' => env('FEATURE_QUERY_OPTIMIZATION', false),
    'cache_improvements' => env('FEATURE_CACHE_IMPROVEMENTS', false),
    'lazy_loading' => env('FEATURE_LAZY_LOADING', false),

    // Testing Features
    'enhanced_test_coverage' => env('FEATURE_ENHANCED_TEST_COVERAGE', true),
    'integration_test_suite' => env('FEATURE_INTEGRATION_TEST_SUITE', true),
    'performance_test_suite' => env('FEATURE_PERFORMANCE_TEST_SUITE', false),

    // Development Features
    'debug_mode_enhancements' => env('FEATURE_DEBUG_MODE_ENHANCEMENTS', false),
    'development_tools' => env('FEATURE_DEVELOPMENT_TOOLS', false),
    'api_versioning' => env('FEATURE_API_VERSIONING', false),

    // Security Features
    'enhanced_csrf_protection' => env('FEATURE_ENHANCED_CSRF_PROTECTION', false),
    'rate_limiting_improvements' => env('FEATURE_RATE_LIMITING_IMPROVEMENTS', false),
    'audit_logging' => env('FEATURE_AUDIT_LOGGING', false),

];