// Global test setup for Vue components
import { config } from '@vue/test-utils';
import Vue from 'vue';

// Configure Vue Test Utils globally
config.mocks = {
  $t: (msg) => msg, // Mock for internationalization if needed
};

// Mock window properties that may be used in components
Object.defineProperty(window, 'location', {
  value: {
    href: 'http://localhost',
    origin: 'http://localhost',
    protocol: 'http:',
    hostname: 'localhost',
    port: '',
    pathname: '/',
    search: '',
    hash: '',
  },
  writable: true,
});

// Mock Laravel's CSRF token meta tag
const meta = document.createElement('meta');
meta.setAttribute('name', 'csrf-token');
meta.setAttribute('content', 'test-csrf-token');
document.head.appendChild(meta);

// Mock axios defaults that Laravel Mix would set
global.axios = {
  defaults: {
    headers: {
      common: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': 'test-csrf-token',
      },
    },
  },
  get: jest.fn(() => Promise.resolve({ data: {} })),
  post: jest.fn(() => Promise.resolve({ data: {} })),
  put: jest.fn(() => Promise.resolve({ data: {} })),
  delete: jest.fn(() => Promise.resolve({ data: {} })),
};

// Make axios available globally
window.axios = global.axios;

// Mock jQuery for Bootstrap components
global.$ = global.jQuery = jest.fn(() => ({
  on: jest.fn(),
  off: jest.fn(),
  trigger: jest.fn(),
  addClass: jest.fn(),
  removeClass: jest.fn(),
  toggleClass: jest.fn(),
  hasClass: jest.fn(() => false),
  attr: jest.fn(),
  removeAttr: jest.fn(),
  val: jest.fn(),
  text: jest.fn(),
  html: jest.fn(),
  show: jest.fn(),
  hide: jest.fn(),
  fadeIn: jest.fn(),
  fadeOut: jest.fn(),
  modal: jest.fn(),
  dropdown: jest.fn(),
  tooltip: jest.fn(),
  popover: jest.fn(),
}));

// Suppress console warnings during tests
global.console = {
  ...console,
  warn: jest.fn(),
  error: jest.fn(),
};