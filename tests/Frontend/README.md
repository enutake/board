# Frontend Testing Documentation

## Overview

This directory contains comprehensive frontend testing infrastructure for Vue.js components and their integration with Laravel backend and Bootstrap CSS framework.

## Test Structure

```
tests/Frontend/
├── README.md                           # This documentation
├── setup.js                           # Global Jest test configuration
├── Components/                         # Vue component unit tests
│   └── ExampleComponent.test.js        # Tests for ExampleComponent.vue
├── Integration/                        # Integration tests
│   ├── VueBootstrapIntegration.test.js # Vue/Bootstrap integration tests
│   └── ApiIntegration.test.js          # Frontend-Backend API tests
└── UIRegression/                       # UI regression tests
    └── ComponentRegression.test.js     # Component structure regression tests
```

## JavaScript Testing Framework

### Jest Configuration

The frontend testing uses Jest with the following setup:

- **Test Environment**: jsdom for DOM simulation
- **Vue Support**: @vue/vue2-jest transformer for Vue 2.7.16
- **Babel Integration**: ES6+ support with @babel/preset-env
- **Module Resolution**: Alias support for `@/` to `resources/js/`

### Dependencies

The following packages are added to support frontend testing:

```json
{
  "@babel/core": "^7.24.0",
  "@babel/preset-env": "^7.24.0",
  "@vue/test-utils": "^1.3.6", 
  "@vue/vue2-jest": "^29.2.6",
  "babel-jest": "^29.7.0",
  "jest": "^29.7.0",
  "jest-environment-jsdom": "^29.7.0"
}
```

### Test Scripts

Run tests using the following npm commands:

```bash
# Run all frontend tests
npm test

# Run tests in watch mode
npm run test:watch

# Run tests with coverage report
npm run test:coverage
```

## Test Categories

### 1. Component Unit Tests

**Location**: `tests/Frontend/Components/`

Tests individual Vue components in isolation:

- **Structure Testing**: Verifies correct HTML structure and CSS classes
- **Props/Data Testing**: Validates component state and properties
- **Event Testing**: Tests component event emission and handling
- **Lifecycle Testing**: Verifies Vue lifecycle hooks (mounted, etc.)

**Example**: `ExampleComponent.test.js`
- Tests Bootstrap card structure
- Verifies responsive grid classes
- Validates component mounting and lifecycle

### 2. Vue/Bootstrap Integration Tests

**Location**: `tests/Frontend/Integration/VueBootstrapIntegration.test.js`

Tests integration between Vue.js and Bootstrap CSS framework:

- **Layout Classes**: Container, row, column responsive classes
- **Component Classes**: Card, button, alert components
- **Dynamic Classes**: Vue-controlled class binding
- **Responsive Design**: Breakpoint-specific classes

### 3. API Integration Tests

**Location**: `tests/Frontend/Integration/ApiIntegration.test.js`

Tests frontend-backend communication:

- **CSRF Token Handling**: Laravel CSRF protection integration
- **Question/Answer APIs**: CRUD operation testing
- **Authentication**: User authentication flow testing
- **Error Handling**: Validation errors, server errors, network failures
- **Request Configuration**: JSON and FormData submissions

### 4. UI Regression Tests

**Location**: `tests/Frontend/UIRegression/ComponentRegression.test.js`

Prevents UI regressions by testing component structure:

- **Question List Components**: Home page question listing
- **Question Detail Components**: Individual question view with answers
- **Navigation Components**: Authenticated/guest navigation states
- **Form Components**: Question/answer creation forms with validation

## Backend Integration Testing

**Location**: `tests/Feature/VueFrontendIntegrationTest.php`

PHP/PHPUnit tests that verify Vue.js integration with Laravel:

- **Vue App Structure**: Verifies `#app` div and asset loading
- **CSRF Integration**: Tests CSRF token availability for Vue components
- **Data Provision**: Ensures backend data is available to frontend
- **Form Submissions**: Tests Vue component form submissions with CSRF
- **API Endpoints**: Validates JSON API responses for Vue
- **Bootstrap Classes**: Verifies CSS framework availability
- **Localization**: Tests Japanese language string integration

## Testing Best Practices

### 1. Component Testing

```javascript
// Always destroy components after testing
afterEach(() => {
  wrapper.destroy();
});

// Use localVue for isolated testing
const localVue = createLocalVue();
const wrapper = mount(Component, { localVue });
```

### 2. Async Testing

```javascript
// Wait for Vue updates
await wrapper.vm.$nextTick();

// Test async operations
await wrapper.find('button').trigger('click');
```

### 3. Mock Setup

```javascript
// Mock axios for API testing
jest.mock('axios');
const mockedAxios = axios;
mockedAxios.get.mockResolvedValue({ data: {} });
```

### 4. Bootstrap Testing

```javascript
// Test CSS classes
expect(wrapper.find('.btn.btn-primary').exists()).toBe(true);
expect(wrapper.classes()).toContain('container');
```

## Running Tests

### Prerequisites

1. **Install Dependencies**:
   ```bash
   npm install
   ```

2. **Laravel Environment**:
   - Ensure Laravel test environment is properly configured
   - Database should be set up for feature tests

### Frontend Tests

```bash
# Run all Jest tests
npm test

# Run specific test file
npm test -- Components/ExampleComponent.test.js

# Run tests with coverage
npm run test:coverage

# Watch mode for development
npm run test:watch
```

### Backend Integration Tests

```bash
# Run specific Laravel test
vendor/bin/phpunit tests/Feature/VueFrontendIntegrationTest.php

# Run all feature tests
vendor/bin/phpunit tests/Feature/

# Run all tests
vendor/bin/phpunit
```

## Coverage Reports

Frontend test coverage is generated in:
- **HTML Reports**: `tests/coverage/frontend/index.html`
- **Text Output**: Console summary during test runs
- **Clover XML**: `tests/coverage/frontend/clover.xml`

## Troubleshooting

### Common Issues

1. **Jest Config Not Found**:
   - Ensure `jest.config.js` is in project root
   - Check package.json test scripts

2. **Vue Component Not Found**:
   - Verify file paths in moduleNameMapping
   - Check component import paths

3. **Bootstrap Classes Missing**:
   - Ensure Bootstrap CSS is properly loaded
   - Check for CSS class assertions in tests

4. **CSRF Token Issues**:
   - Verify meta tag setup in test environment
   - Check axios mock configuration

### Debug Commands

```bash
# Check Jest configuration
npx jest --showConfig

# Run single test with verbose output
npm test -- --verbose ComponentName.test.js

# Debug specific test
npm test -- --debug ComponentName.test.js
```

## Integration with CI/CD

The frontend tests can be integrated into CI/CD pipelines:

```yaml
# Example GitHub Actions step
- name: Run Frontend Tests
  run: |
    npm install
    npm run test:coverage

- name: Run Backend Integration Tests  
  run: |
    composer install
    vendor/bin/phpunit tests/Feature/VueFrontendIntegrationTest.php
```

## Future Enhancements

1. **E2E Testing**: Add Cypress or Playwright for full user journey testing
2. **Visual Regression**: Add screenshot comparison testing
3. **Performance Testing**: Add component performance benchmarks
4. **Accessibility Testing**: Add a11y testing with jest-axe
5. **Component Library**: Expand component test coverage as the app grows

## Maintenance

- **Update Dependencies**: Keep Jest and Vue testing utilities up to date
- **Expand Coverage**: Add tests for new Vue components as they're created
- **Refactor Tests**: Keep tests maintainable as components evolve
- **Documentation**: Update this README as testing infrastructure changes