import { mount, createLocalVue } from '@vue/test-utils';
import Vue from 'vue';

const localVue = createLocalVue();

describe('Vue and Bootstrap Integration', () => {
  describe('Bootstrap CSS Classes Integration', () => {
    let testComponent;

    beforeAll(() => {
      // Create a test component that uses Bootstrap classes
      testComponent = Vue.extend({
        template: `
          <div class="container">
            <div class="row">
              <div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title mb-0">Test Card</h5>
                  </div>
                  <div class="card-body">
                    <p class="card-text">This is a test card with Bootstrap classes.</p>
                    <button class="btn btn-primary" @click="handleClick">Click me</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        methods: {
          handleClick() {
            this.$emit('click-event');
          }
        }
      });
    });

    it('renders Bootstrap layout classes correctly', () => {
      const wrapper = mount(testComponent, { localVue });
      
      expect(wrapper.find('.container').exists()).toBe(true);
      expect(wrapper.find('.row').exists()).toBe(true);
      expect(wrapper.find('.col-12.col-md-6').exists()).toBe(true);
      
      wrapper.destroy();
    });

    it('renders Bootstrap card component correctly', () => {
      const wrapper = mount(testComponent, { localVue });
      
      expect(wrapper.find('.card').exists()).toBe(true);
      expect(wrapper.find('.card-header').exists()).toBe(true);
      expect(wrapper.find('.card-body').exists()).toBe(true);
      expect(wrapper.find('.card-title').exists()).toBe(true);
      expect(wrapper.find('.card-text').exists()).toBe(true);
      
      wrapper.destroy();
    });

    it('renders Bootstrap button classes correctly', () => {
      const wrapper = mount(testComponent, { localVue });
      
      const button = wrapper.find('.btn.btn-primary');
      expect(button.exists()).toBe(true);
      expect(button.classes()).toContain('btn');
      expect(button.classes()).toContain('btn-primary');
      
      wrapper.destroy();
    });

    it('handles Vue event binding with Bootstrap elements', async () => {
      const wrapper = mount(testComponent, { localVue });
      
      const button = wrapper.find('.btn');
      await button.trigger('click');
      
      expect(wrapper.emitted('click-event')).toBeTruthy();
      expect(wrapper.emitted('click-event')).toHaveLength(1);
      
      wrapper.destroy();
    });
  });

  describe('Vue App Integration', () => {
    it('can mount Vue components in #app container', () => {
      // Create a test component
      const TestApp = {
        template: '<div class="test-app">Vue App Mounted</div>'
      };
      
      // Mount the component using @vue/test-utils
      const wrapper = mount(TestApp, {
        localVue,
        attachTo: document.body
      });
      
      // Check if the component is mounted correctly
      expect(wrapper.exists()).toBe(true);
      expect(wrapper.classes()).toContain('test-app');
      expect(wrapper.text()).toBe('Vue App Mounted');
      
      wrapper.destroy();
    });

    it('preserves Bootstrap classes when Vue updates DOM', async () => {
      const DynamicComponent = Vue.extend({
        template: `
          <div class="container">
            <div class="alert" :class="alertClass">
              {{ message }}
            </div>
            <button class="btn btn-success" @click="toggleAlert">Toggle Alert</button>
          </div>
        `,
        data() {
          return {
            message: 'Initial message',
            isSuccess: true
          };
        },
        computed: {
          alertClass() {
            return this.isSuccess ? 'alert-success' : 'alert-danger';
          }
        },
        methods: {
          toggleAlert() {
            this.isSuccess = !this.isSuccess;
            this.message = this.isSuccess ? 'Success message' : 'Error message';
          }
        }
      });

      const wrapper = mount(DynamicComponent, { localVue });
      
      // Check initial state
      expect(wrapper.find('.alert-success').exists()).toBe(true);
      expect(wrapper.find('.alert-danger').exists()).toBe(false);
      expect(wrapper.find('.alert').text()).toBe('Initial message');
      
      // Trigger change
      await wrapper.find('.btn').trigger('click');
      
      // Check updated state
      expect(wrapper.find('.alert-success').exists()).toBe(false);
      expect(wrapper.find('.alert-danger').exists()).toBe(true);
      expect(wrapper.find('.alert').text()).toBe('Error message');
      
      wrapper.destroy();
    });
  });

  describe('Responsive Bootstrap Classes with Vue', () => {
    let responsiveComponent;

    beforeAll(() => {
      responsiveComponent = Vue.extend({
        template: `
          <div class="container-fluid">
            <div class="row">
              <div class="col-12 col-sm-6 col-md-4 col-lg-3" 
                   v-for="item in items" 
                   :key="item.id">
                <div class="card mb-3">
                  <div class="card-body">
                    <h5 class="card-title">{{ item.title }}</h5>
                    <p class="card-text">{{ item.content }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        data() {
          return {
            items: [
              { id: 1, title: 'Item 1', content: 'Content 1' },
              { id: 2, title: 'Item 2', content: 'Content 2' },
              { id: 3, title: 'Item 3', content: 'Content 3' }
            ]
          };
        }
      });
    });

    it('renders responsive grid classes correctly', () => {
      const wrapper = mount(responsiveComponent, { localVue });
      
      const columns = wrapper.findAll('.col-12.col-sm-6.col-md-4.col-lg-3');
      expect(columns).toHaveLength(3);
      
      columns.wrappers.forEach(col => {
        expect(col.classes()).toContain('col-12');
        expect(col.classes()).toContain('col-sm-6');
        expect(col.classes()).toContain('col-md-4');
        expect(col.classes()).toContain('col-lg-3');
      });
      
      wrapper.destroy();
    });

    it('renders Vue list with Bootstrap cards correctly', () => {
      const wrapper = mount(responsiveComponent, { localVue });
      
      const cards = wrapper.findAll('.card');
      expect(cards).toHaveLength(3);
      
      cards.wrappers.forEach((card, index) => {
        const expectedTitle = `Item ${index + 1}`;
        const expectedContent = `Content ${index + 1}`;
        
        expect(card.find('.card-title').text()).toBe(expectedTitle);
        expect(card.find('.card-text').text()).toBe(expectedContent);
      });
      
      wrapper.destroy();
    });
  });
});