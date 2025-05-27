module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'json', 'vue'],
  transform: {
    '^.+\\.vue$': '@vue/vue2-jest',
    '^.+\\.js$': 'babel-jest'
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1'
  },
  testMatch: [
    '**/tests/Frontend/**/*.test.js',
    '**/tests/Frontend/**/*.spec.js'
  ],
  collectCoverageFrom: [
    'resources/js/**/*.{js,vue}',
    '!resources/js/app.js',
    '!**/node_modules/**'
  ],
  coverageDirectory: 'tests/coverage/frontend',
  coverageReporters: ['html', 'text', 'clover'],
  setupFilesAfterEnv: ['<rootDir>/tests/Frontend/setup.js']
};