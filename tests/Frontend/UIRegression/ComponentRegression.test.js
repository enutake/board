import { mount, createLocalVue } from '@vue/test-utils';
import Vue from 'vue';

const localVue = createLocalVue();

describe('UI Regression Tests', () => {
  describe('Question List Component Regression', () => {
    let QuestionListComponent;

    beforeAll(() => {
      // Simulate the question list structure from home.blade.php
      QuestionListComponent = Vue.extend({
        template: `
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div v-for="question in questions" :key="question.id" class="card mb-4">
                  <div class="card-header">{{ question.title }}</div>
                  <div class="card-body">
                    <div class="card-date text-right small">{{ formatDate(question.created_at) }}</div>
                    <div class="card-text mb-3">{{ question.content }}</div>
                    <div class="card-more-detail text-center">
                      <a class="btn btn-primary stretched-link" :href="getQuestionUrl(question.id)">
                        この質問の回答を見る
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        props: {
          questions: {
            type: Array,
            default: () => []
          }
        },
        methods: {
          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ja-JP', {
              year: 'numeric',
              month: '2-digit',
              day: '2-digit'
            });
          },
          getQuestionUrl(id) {
            return `/questions/${id}`;
          }
        }
      });
    });

    it('renders question list with correct Bootstrap structure', () => {
      const mockQuestions = [
        {
          id: 1,
          title: 'テスト質問1',
          content: 'テスト内容1',
          created_at: '2024-01-01T00:00:00Z'
        },
        {
          id: 2,
          title: 'テスト質問2',
          content: 'テスト内容2',
          created_at: '2024-01-02T00:00:00Z'
        }
      ];

      const wrapper = mount(QuestionListComponent, {
        localVue,
        propsData: { questions: mockQuestions }
      });

      // Check Bootstrap layout structure
      expect(wrapper.find('.container').exists()).toBe(true);
      expect(wrapper.find('.row.justify-content-center').exists()).toBe(true);
      expect(wrapper.find('.col-md-8').exists()).toBe(true);

      // Check question cards
      const cards = wrapper.findAll('.card.mb-4');
      expect(cards).toHaveLength(2);

      // Check card structure
      cards.wrappers.forEach((card, index) => {
        expect(card.find('.card-header').exists()).toBe(true);
        expect(card.find('.card-body').exists()).toBe(true);
        expect(card.find('.card-date.text-right.small').exists()).toBe(true);
        expect(card.find('.card-text.mb-3').exists()).toBe(true);
        expect(card.find('.card-more-detail.text-center').exists()).toBe(true);
        expect(card.find('.btn.btn-primary.stretched-link').exists()).toBe(true);
      });

      wrapper.destroy();
    });

    it('maintains consistent card styling across multiple questions', () => {
      const mockQuestions = Array.from({ length: 5 }, (_, i) => ({
        id: i + 1,
        title: `質問 ${i + 1}`,
        content: `内容 ${i + 1}`,
        created_at: '2024-01-01T00:00:00Z'
      }));

      const wrapper = mount(QuestionListComponent, {
        localVue,
        propsData: { questions: mockQuestions }
      });

      const cards = wrapper.findAll('.card.mb-4');
      expect(cards).toHaveLength(5);

      // Ensure all cards have consistent styling
      cards.wrappers.forEach(card => {
        expect(card.classes()).toContain('card');
        expect(card.classes()).toContain('mb-4');
        
        const header = card.find('.card-header');
        const body = card.find('.card-body');
        const button = card.find('.btn.btn-primary.stretched-link');
        
        expect(header.exists()).toBe(true);
        expect(body.exists()).toBe(true);
        expect(button.exists()).toBe(true);
        expect(button.text()).toBe('この質問の回答を見る');
      });

      wrapper.destroy();
    });
  });

  describe('Question Detail Component Regression', () => {
    let QuestionDetailComponent;

    beforeAll(() => {
      // Simulate the question detail structure from question/index.blade.php
      QuestionDetailComponent = Vue.extend({
        template: `
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <!-- Question Card -->
                <div class="card mb-4">
                  <div class="card-header">{{ question.title }}</div>
                  <div class="card-body">
                    <div class="card-date text-right small">{{ formatDate(question.created_at) }}</div>
                    <div class="card-text mb-3">{{ question.content }}</div>
                    <div class="card-user text-right small">{{ question.user.name }}さん</div>
                  </div>
                </div>
                
                <!-- Answer Button -->
                <div class="text-center mb-4">
                  <a :href="getAnswerCreateUrl(question.id)" 
                     class="answer-question-btn btn btn-primary text-center">
                    この質問に回答する
                  </a>
                </div>
                
                <!-- Answers -->
                <div v-for="answer in answers" :key="answer.id" class="card mb-4">
                  <div class="card-body">
                    <div class="card-date text-right small">{{ formatDate(answer.created_at) }}</div>
                    <div class="card-text mb-3">{{ answer.content }}</div>
                    <div class="card-user text-right small">{{ answer.user.name }}さん</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        props: {
          question: {
            type: Object,
            required: true
          },
          answers: {
            type: Array,
            default: () => []
          }
        },
        methods: {
          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ja-JP', {
              year: 'numeric',
              month: '2-digit',
              day: '2-digit'
            });
          },
          getAnswerCreateUrl(questionId) {
            return `/questions/${questionId}/answers/new`;
          }
        }
      });
    });

    it('renders question detail with correct structure', () => {
      const mockQuestion = {
        id: 1,
        title: 'テスト質問',
        content: 'テスト質問の詳細内容',
        created_at: '2024-01-01T00:00:00Z',
        user: { name: 'テストユーザー' }
      };

      const mockAnswers = [
        {
          id: 1,
          content: '回答1の内容',
          created_at: '2024-01-01T01:00:00Z',
          user: { name: '回答者1' }
        },
        {
          id: 2,
          content: '回答2の内容',
          created_at: '2024-01-01T02:00:00Z',
          user: { name: '回答者2' }
        }
      ];

      const wrapper = mount(QuestionDetailComponent, {
        localVue,
        propsData: { question: mockQuestion, answers: mockAnswers }
      });

      // Check layout structure
      expect(wrapper.find('.container').exists()).toBe(true);
      expect(wrapper.find('.row.justify-content-center').exists()).toBe(true);
      expect(wrapper.find('.col-md-8').exists()).toBe(true);

      // Check question card
      const questionCard = wrapper.findAll('.card').at(0);
      expect(questionCard.find('.card-header').text()).toBe('テスト質問');
      expect(questionCard.find('.card-text').text()).toBe('テスト質問の詳細内容');
      expect(questionCard.find('.card-user').text()).toBe('テストユーザーさん');

      // Check answer button
      const answerButton = wrapper.find('.answer-question-btn.btn.btn-primary');
      expect(answerButton.exists()).toBe(true);
      expect(answerButton.text()).toBe('この質問に回答する');

      // Check answer cards
      const allCards = wrapper.findAll('.card');
      expect(allCards).toHaveLength(3); // 1 question + 2 answers

      wrapper.destroy();
    });

    it('maintains consistent styling for answers', () => {
      const mockQuestion = {
        id: 1,
        title: 'テスト質問',
        content: 'テスト内容',
        created_at: '2024-01-01T00:00:00Z',
        user: { name: 'テストユーザー' }
      };

      const mockAnswers = Array.from({ length: 3 }, (_, i) => ({
        id: i + 1,
        content: `回答 ${i + 1} の内容`,
        created_at: '2024-01-01T00:00:00Z',
        user: { name: `回答者 ${i + 1}` }
      }));

      const wrapper = mount(QuestionDetailComponent, {
        localVue,
        propsData: { question: mockQuestion, answers: mockAnswers }
      });

      const allCards = wrapper.findAll('.card');
      // Skip first card (question) and check answer cards
      for (let i = 1; i < allCards.length; i++) {
        const answerCard = allCards.at(i);
        expect(answerCard.find('.card-body').exists()).toBe(true);
        expect(answerCard.find('.card-date.text-right.small').exists()).toBe(true);
        expect(answerCard.find('.card-text.mb-3').exists()).toBe(true);
        expect(answerCard.find('.card-user.text-right.small').exists()).toBe(true);
      }

      wrapper.destroy();
    });
  });

  describe('Navigation Component Regression', () => {
    let NavigationComponent;

    beforeAll(() => {
      // Simulate the navigation structure from app.blade.php
      NavigationComponent = Vue.extend({
        template: `
          <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
              <a class="navbar-brand" href="/">{{ appName }}</a>
              <button class="navbar-toggler" type="button" 
                      data-toggle="collapse" 
                      data-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
              </button>
              
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto"></ul>
                <ul class="navbar-nav ml-auto">
                  <li v-if="!isAuthenticated" class="nav-item">
                    <a class="nav-link" href="/login">ログイン</a>
                  </li>
                  <li v-if="!isAuthenticated" class="nav-item">
                    <a class="nav-link" href="/register">新規登録</a>
                  </li>
                  <li v-if="isAuthenticated" class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       data-toggle="dropdown">{{ userName }}</a>
                    <div class="dropdown-menu dropdown-menu-right">
                      <a class="dropdown-item" href="/logout">ログアウト</a>
                      <a class="dropdown-item" href="/questions/new">質問を投稿する</a>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </nav>
        `,
        props: {
          appName: {
            type: String,
            default: 'Laravel'
          },
          isAuthenticated: {
            type: Boolean,
            default: false
          },
          userName: {
            type: String,
            default: ''
          }
        }
      });
    });

    it('renders navigation with correct Bootstrap classes for unauthenticated user', () => {
      const wrapper = mount(NavigationComponent, {
        localVue,
        propsData: {
          appName: 'Board App',
          isAuthenticated: false
        }
      });

      // Check navbar structure
      expect(wrapper.find('.navbar.navbar-expand-md.navbar-light.bg-white.shadow-sm').exists()).toBe(true);
      expect(wrapper.find('.container').exists()).toBe(true);
      expect(wrapper.find('.navbar-brand').exists()).toBe(true);
      expect(wrapper.find('.navbar-toggler').exists()).toBe(true);

      // Check authentication links
      const navLinks = wrapper.findAll('.nav-link');
      expect(navLinks.wrappers.some(link => link.text() === 'ログイン')).toBe(true);
      expect(navLinks.wrappers.some(link => link.text() === '新規登録')).toBe(true);

      // Should not have dropdown
      expect(wrapper.find('.dropdown').exists()).toBe(false);

      wrapper.destroy();
    });

    it('renders navigation with user dropdown for authenticated user', () => {
      const wrapper = mount(NavigationComponent, {
        localVue,
        propsData: {
          appName: 'Board App',
          isAuthenticated: true,
          userName: 'テストユーザー'
        }
      });

      // Should not have login/register links
      const navLinks = wrapper.findAll('.nav-link');
      expect(navLinks.wrappers.some(link => link.text() === 'ログイン')).toBe(false);
      expect(navLinks.wrappers.some(link => link.text() === '新規登録')).toBe(false);

      // Should have dropdown
      expect(wrapper.find('.nav-item.dropdown').exists()).toBe(true);
      expect(wrapper.find('.nav-link.dropdown-toggle').text()).toBe('テストユーザー');

      // Check dropdown menu
      expect(wrapper.find('.dropdown-menu.dropdown-menu-right').exists()).toBe(true);
      const dropdownItems = wrapper.findAll('.dropdown-item');
      expect(dropdownItems.wrappers.some(item => item.text() === 'ログアウト')).toBe(true);
      expect(dropdownItems.wrappers.some(item => item.text() === '質問を投稿する')).toBe(true);

      wrapper.destroy();
    });
  });

  describe('Form Component Regression', () => {
    let FormComponent;

    beforeAll(() => {
      // Generic form component for question/answer creation
      FormComponent = Vue.extend({
        template: `
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="card">
                  <div class="card-header">{{ title }}</div>
                  <div class="card-body">
                    <form @submit.prevent="handleSubmit">
                      <div class="form-group">
                        <label for="title" v-if="showTitle">タイトル</label>
                        <input v-if="showTitle" 
                               type="text" 
                               id="title"
                               v-model="formData.title"
                               class="form-control"
                               :class="{ 'is-invalid': errors.title }"
                               required>
                        <div v-if="errors.title" class="invalid-feedback">
                          {{ errors.title }}
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label for="content">内容</label>
                        <textarea id="content"
                                  v-model="formData.content"
                                  class="form-control"
                                  :class="{ 'is-invalid': errors.content }"
                                  rows="5"
                                  required></textarea>
                        <div v-if="errors.content" class="invalid-feedback">
                          {{ errors.content }}
                        </div>
                      </div>
                      
                      <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">
                          {{ submitText }}
                        </button>
                        <a href="/" class="btn btn-secondary ml-2">キャンセル</a>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `,
        props: {
          title: String,
          showTitle: Boolean,
          submitText: String
        },
        data() {
          return {
            formData: {
              title: '',
              content: ''
            },
            errors: {}
          };
        },
        methods: {
          handleSubmit() {
            this.$emit('submit', this.formData);
          },
          setErrors(errors) {
            this.errors = errors;
          }
        }
      });
    });

    it('renders question form with correct Bootstrap form classes', () => {
      const wrapper = mount(FormComponent, {
        localVue,
        propsData: {
          title: '質問を投稿する',
          showTitle: true,
          submitText: '投稿する'
        }
      });

      // Check form structure
      expect(wrapper.find('form').exists()).toBe(true);
      expect(wrapper.findAll('.form-group')).toHaveLength(3);

      // Check form controls
      expect(wrapper.find('input.form-control').exists()).toBe(true);
      expect(wrapper.find('textarea.form-control').exists()).toBe(true);

      // Check buttons
      expect(wrapper.find('.btn.btn-primary').exists()).toBe(true);
      expect(wrapper.find('.btn.btn-secondary').exists()).toBe(true);

      wrapper.destroy();
    });

    it('displays validation errors with Bootstrap classes', async () => {
      const wrapper = mount(FormComponent, {
        localVue,
        propsData: {
          title: '質問を投稿する',
          showTitle: true,
          submitText: '投稿する'
        }
      });

      // Set validation errors
      await wrapper.vm.setErrors({
        title: 'タイトルは必須です',
        content: '内容は必須です'
      });

      await wrapper.vm.$nextTick();

      // Check error classes
      expect(wrapper.find('input.form-control.is-invalid').exists()).toBe(true);
      expect(wrapper.find('textarea.form-control.is-invalid').exists()).toBe(true);

      // Check error messages
      const errorMessages = wrapper.findAll('.invalid-feedback');
      expect(errorMessages).toHaveLength(2);
      expect(errorMessages.at(0).text()).toBe('タイトルは必須です');
      expect(errorMessages.at(1).text()).toBe('内容は必須です');

      wrapper.destroy();
    });
  });
});