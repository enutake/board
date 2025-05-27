import { mount, createLocalVue } from '@vue/test-utils';
import ExampleComponent from '../../../resources/js/components/ExampleComponent.vue';

const localVue = createLocalVue();

describe('ExampleComponent', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(ExampleComponent, {
      localVue,
    });
  });

  afterEach(() => {
    wrapper.destroy();
  });

  describe('Component Structure', () => {
    it('renders correctly', () => {
      expect(wrapper.exists()).toBe(true);
    });

    it('has the correct HTML structure', () => {
      expect(wrapper.find('.container').exists()).toBe(true);
      expect(wrapper.find('.row.justify-content-center').exists()).toBe(true);
      expect(wrapper.find('.col-md-8').exists()).toBe(true);
      expect(wrapper.find('.card').exists()).toBe(true);
    });

    it('displays the correct header text', () => {
      expect(wrapper.find('.card-header').text()).toBe('Example Component');
    });

    it('displays the correct body text', () => {
      expect(wrapper.find('.card-body').text()).toBe("I'm an example component.");
    });
  });

  describe('Bootstrap Classes', () => {
    it('uses Bootstrap container classes correctly', () => {
      const container = wrapper.find('.container');
      expect(container.exists()).toBe(true);
    });

    it('uses Bootstrap grid classes correctly', () => {
      const row = wrapper.find('.row.justify-content-center');
      const col = wrapper.find('.col-md-8');
      
      expect(row.exists()).toBe(true);
      expect(col.exists()).toBe(true);
    });

    it('uses Bootstrap card classes correctly', () => {
      const card = wrapper.find('.card');
      const cardHeader = wrapper.find('.card-header');
      const cardBody = wrapper.find('.card-body');
      
      expect(card.exists()).toBe(true);
      expect(cardHeader.exists()).toBe(true);
      expect(cardBody.exists()).toBe(true);
    });
  });

  describe('Vue Lifecycle', () => {
    it('calls mounted hook', () => {
      // Mock console.log to capture mounted message
      const consoleSpy = jest.spyOn(console, 'log').mockImplementation();
      
      // Create a new instance to trigger mounted
      const mountedWrapper = mount(ExampleComponent, {
        localVue,
      });
      
      expect(consoleSpy).toHaveBeenCalledWith('Component mounted.');
      
      consoleSpy.mockRestore();
      mountedWrapper.destroy();
    });
  });

  describe('Component Integration', () => {
    it('can be used as a Vue component', () => {
      expect(wrapper.vm).toBeDefined();
      expect(wrapper.vm.$options.name).toBe(undefined); // No explicit name set
    });

    it('has no props by default', () => {
      // ExampleComponent doesn't define any props, so $props might be undefined
      const props = wrapper.vm.$props || {};
      expect(Object.keys(props)).toHaveLength(0);
    });

    it('has no custom data by default', () => {
      // ExampleComponent doesn't define any data, so $data might be empty
      const data = wrapper.vm.$data || {};
      expect(Object.keys(data)).toHaveLength(0);
    });
  });

  describe('Responsive Design', () => {
    it('includes responsive Bootstrap classes', () => {
      const col = wrapper.find('.col-md-8');
      expect(col.classes()).toContain('col-md-8');
    });
  });
});