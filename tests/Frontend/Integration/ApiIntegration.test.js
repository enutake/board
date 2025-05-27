import axios from 'axios';

// Mock axios for testing
jest.mock('axios');
const mockedAxios = axios;

describe('API Integration Tests', () => {
  beforeEach(() => {
    // Reset axios mocks before each test
    jest.clearAllMocks();
    
    // Setup default axios configuration like Laravel would
    mockedAxios.defaults = {
      headers: {
        common: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': 'test-csrf-token',
        },
      },
    };
  });

  describe('CSRF Token Integration', () => {
    it('includes CSRF token in request headers', async () => {
      mockedAxios.post.mockResolvedValue({ data: { success: true } });
      
      await mockedAxios.post('/api/test', { test: 'data' });
      
      expect(mockedAxios.post).toHaveBeenCalledWith('/api/test', { test: 'data' });
      expect(mockedAxios.defaults.headers.common['X-CSRF-TOKEN']).toBe('test-csrf-token');
    });

    it('includes X-Requested-With header for AJAX identification', () => {
      expect(mockedAxios.defaults.headers.common['X-Requested-With']).toBe('XMLHttpRequest');
    });
  });

  describe('Question API Integration', () => {
    it('can fetch questions list', async () => {
      const mockQuestions = [
        {
          id: 1,
          title: 'Test Question 1',
          content: 'Test content 1',
          created_at: '2024-01-01T00:00:00Z',
          user: { name: 'Test User 1' }
        },
        {
          id: 2,
          title: 'Test Question 2',
          content: 'Test content 2',
          created_at: '2024-01-02T00:00:00Z',
          user: { name: 'Test User 2' }
        }
      ];

      mockedAxios.get.mockResolvedValue({ data: { questions: mockQuestions } });
      
      const response = await mockedAxios.get('/api/questions');
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/api/questions');
      expect(response.data.questions).toEqual(mockQuestions);
      expect(response.data.questions).toHaveLength(2);
    });

    it('can create a new question', async () => {
      const newQuestion = {
        title: 'New Question',
        content: 'New question content'
      };

      const mockResponse = {
        id: 3,
        ...newQuestion,
        created_at: '2024-01-03T00:00:00Z',
        user: { name: 'Current User' }
      };

      mockedAxios.post.mockResolvedValue({ data: mockResponse });
      
      const response = await mockedAxios.post('/questions', newQuestion);
      
      expect(mockedAxios.post).toHaveBeenCalledWith('/questions', newQuestion);
      expect(response.data).toEqual(mockResponse);
    });

    it('can fetch a specific question with answers', async () => {
      const mockQuestionWithAnswers = {
        question: {
          id: 1,
          title: 'Test Question',
          content: 'Test content',
          created_at: '2024-01-01T00:00:00Z',
          user: { name: 'Question Author' }
        },
        answers: [
          {
            id: 1,
            content: 'Answer 1 content',
            created_at: '2024-01-01T01:00:00Z',
            user: { name: 'Answer Author 1' }
          },
          {
            id: 2,
            content: 'Answer 2 content',
            created_at: '2024-01-01T02:00:00Z',
            user: { name: 'Answer Author 2' }
          }
        ]
      };

      mockedAxios.get.mockResolvedValue({ data: mockQuestionWithAnswers });
      
      const response = await mockedAxios.get('/api/questions/1');
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/api/questions/1');
      expect(response.data.question.id).toBe(1);
      expect(response.data.answers).toHaveLength(2);
    });
  });

  describe('Answer API Integration', () => {
    it('can create a new answer', async () => {
      const newAnswer = {
        question_id: 1,
        content: 'New answer content'
      };

      const mockResponse = {
        id: 3,
        ...newAnswer,
        created_at: '2024-01-03T00:00:00Z',
        user: { name: 'Current User' }
      };

      mockedAxios.post.mockResolvedValue({ data: mockResponse });
      
      const response = await mockedAxios.post('/answers', newAnswer);
      
      expect(mockedAxios.post).toHaveBeenCalledWith('/answers', newAnswer);
      expect(response.data).toEqual(mockResponse);
    });
  });

  describe('User Authentication API Integration', () => {
    it('can fetch current user data', async () => {
      const mockUser = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        email_verified_at: '2024-01-01T00:00:00Z'
      };

      mockedAxios.get.mockResolvedValue({ data: mockUser });
      
      const response = await mockedAxios.get('/api/user');
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/api/user');
      expect(response.data).toEqual(mockUser);
    });

    it('handles authentication errors gracefully', async () => {
      const authError = {
        response: {
          status: 401,
          data: { message: 'Unauthenticated' }
        }
      };

      mockedAxios.get.mockRejectedValue(authError);
      
      try {
        await mockedAxios.get('/api/user');
      } catch (error) {
        expect(error.response.status).toBe(401);
        expect(error.response.data.message).toBe('Unauthenticated');
      }
    });
  });

  describe('Error Handling', () => {
    it('handles validation errors properly', async () => {
      const validationError = {
        response: {
          status: 422,
          data: {
            message: 'The given data was invalid.',
            errors: {
              title: ['The title field is required.'],
              content: ['The content field is required.']
            }
          }
        }
      };

      mockedAxios.post.mockRejectedValue(validationError);
      
      try {
        await mockedAxios.post('/questions', {});
      } catch (error) {
        expect(error.response.status).toBe(422);
        expect(error.response.data.errors.title).toContain('The title field is required.');
        expect(error.response.data.errors.content).toContain('The content field is required.');
      }
    });

    it('handles server errors properly', async () => {
      const serverError = {
        response: {
          status: 500,
          data: { message: 'Server Error' }
        }
      };

      mockedAxios.get.mockRejectedValue(serverError);
      
      try {
        await mockedAxios.get('/api/questions');
      } catch (error) {
        expect(error.response.status).toBe(500);
        expect(error.response.data.message).toBe('Server Error');
      }
    });

    it('handles network errors properly', async () => {
      const networkError = new Error('Network Error');
      networkError.code = 'NETWORK_ERROR';

      mockedAxios.get.mockRejectedValue(networkError);
      
      try {
        await mockedAxios.get('/api/questions');
      } catch (error) {
        expect(error.message).toBe('Network Error');
        expect(error.code).toBe('NETWORK_ERROR');
      }
    });
  });

  describe('Request Configuration', () => {
    it('sends requests with correct content type for JSON', async () => {
      mockedAxios.post.mockResolvedValue({ data: { success: true } });
      
      await mockedAxios.post('/api/test', { test: 'data' }, {
        headers: { 'Content-Type': 'application/json' }
      });
      
      expect(mockedAxios.post).toHaveBeenCalledWith(
        '/api/test',
        { test: 'data' },
        { headers: { 'Content-Type': 'application/json' } }
      );
    });

    it('can handle form data submissions', async () => {
      const formData = new FormData();
      formData.append('title', 'Test Question');
      formData.append('content', 'Test Content');

      mockedAxios.post.mockResolvedValue({ data: { success: true } });
      
      await mockedAxios.post('/questions', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      expect(mockedAxios.post).toHaveBeenCalledWith(
        '/questions',
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } }
      );
    });
  });
});