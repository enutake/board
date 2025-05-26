# Test Infrastructure Documentation

## Overview

This document outlines the test infrastructure setup for the Board application, implemented as part of Phase 0A of the version upgrade project.

## Directory Structure

```
tests/
├── README.md              # This documentation
├── TestCase.php           # Base test case with database setup
├── TestHelpers.php        # Common test utilities and factory helpers
├── TestConfig.php         # Test configuration constants
├── FeatureTestCase.php    # Base class for feature tests
├── CreatesApplication.php # Laravel application creation trait
├── Unit/                  # Unit tests
│   ├── Repositories/      # Repository layer tests
│   └── Services/          # Service layer tests
├── Feature/               # Feature/integration tests
└── coverage/              # Test coverage reports (generated)
    ├── html/              # HTML coverage reports
    └── clover.xml         # Clover coverage format
```

## Test Database Configuration

### Separate Test Database
- **Connection Name**: `mysql_testing`
- **Database Name**: `board_testing`
- **Environment Variable**: `DB_TEST_DATABASE`

### Configuration Files
- **Database Config**: `config/database.php` - Added `mysql_testing` connection
- **PHPUnit Config**: `phpunit.xml` - Updated to use test database and coverage reporting

## Factory Improvements

### Enhanced Data Generation
All factories now use proper relationships instead of hardcoded IDs:

```php
// Before (hardcoded)
'user_id' => 1,

// After (proper relationships)
'user_id' => function () {
    return factory(\App\Models\User::class)->create()->id;
},
```

### Factory States
Added factory states for different scenarios:

**UserFactory**:
- `unverified`: Users without email verification
- `admin`: Administrative users

**QuestionFactory**:
- `short`: Questions with brief content
- `long`: Questions with detailed content

**AnswerFactory**:
- `short`: Brief answers
- `detailed`: Comprehensive answers

## Test Helpers

### TestHelpers Trait
Provides convenient methods for test data creation:

```php
// Create single models
$user = $this->createUser();
$question = $this->createQuestion();
$answer = $this->createAnswer();

// Create multiple models
$users = $this->createUsers(5);
$questions = $this->createQuestions(10);

// Create related data
$questionWithAnswers = $this->createQuestionWithAnswers();

// Authentication helpers
$this->actingAsUser();
$this->actingAsAdmin();

// Database assertions
$this->assertDatabaseHasModel($model);
$this->assertDatabaseMissingModel($model);
```

### FeatureTestCase
Base class for feature tests with additional helpers:

```php
// JSON API testing
$this->assertJsonApiResponse(200, $structure);
$this->assertJsonSuccess($data);
$this->assertJsonError($message, 422);

// Validation testing
$this->assertValidationError(['field1', 'field2']);

// Redirect testing
$this->assertRedirectResponse('/path');
```

## Test Configuration

### TestConfig Class
Centralized configuration for:
- Common test values and constants
- Database connection settings
- Factory defaults
- User credentials for testing
- HTTP status codes

### Environment Settings
Tests automatically use:
- `APP_ENV=testing`
- `DB_CONNECTION=mysql_testing`
- `CACHE_DRIVER=array`
- `SESSION_DRIVER=array`
- `QUEUE_CONNECTION=sync`

## Coverage Reporting

### Enabled Coverage Types
- **HTML Reports**: `tests/coverage/html/index.html`
- **Clover XML**: `tests/coverage/clover.xml`
- **Console Output**: Shows coverage summary

### Coverage Configuration
- **Included**: All `app/` directory files
- **Excluded**: Console commands, TrustHosts, TrustProxies middleware
- **Strict Mode**: Enabled for better test quality

## Running Tests

### Basic Commands
```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-html tests/coverage/html

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature

# Run specific test file
./vendor/bin/phpunit tests/Unit/Repositories/AnswerRepositoryTest.php
```

### Test Database Setup
Ensure the test database exists:
```bash
mysql -u username -p -e "CREATE DATABASE board_testing;"
```

## Best Practices

### 1. Use Helpers
Always use the TestHelpers trait for data creation:
```php
use Tests\TestHelpers;

class MyTest extends TestCase
{
    use TestHelpers;
    
    public function test_example()
    {
        $user = $this->createUser();
        // ... test logic
    }
}
```

### 2. Database Refresh
Use `RefreshDatabase` trait (included in TestHelpers) to ensure clean state:
```php
// TestHelpers already includes RefreshDatabase
use TestHelpers; // This automatically includes RefreshDatabase
```

### 3. Proper Assertions
Use specific assertions for better error messages:
```php
// Better
$this->assertDatabaseHasModel($user, ['name' => 'Updated Name']);

// Instead of
$this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
```

### 4. Test Organization
- **Unit Tests**: Test individual methods in isolation
- **Feature Tests**: Test complete workflows and HTTP requests
- **Use FeatureTestCase**: For tests involving HTTP requests

## Next Steps (Phase 0B)

After completing Phase 0A infrastructure, the next phase will focus on:
1. Expanding test coverage for repositories
2. Adding comprehensive service layer tests
3. Creating model relationship tests
4. Implementing controller feature tests

## Troubleshooting

### Common Issues
1. **Database Connection**: Ensure `board_testing` database exists
2. **Coverage Reports**: Requires Xdebug extension for PHP
3. **Factory Errors**: Check model relationships are properly defined
4. **Memory Issues**: Increase PHP memory limit if needed

### Debug Commands
```bash
# Check database connection
./vendor/bin/phpunit --filter test_database_connection

# Verify factory definitions
php artisan tinker
>>> factory(\App\Models\User::class)->make()
```