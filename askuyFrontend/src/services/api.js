import axios from 'axios';
import { initialData } from '../data/initialData';

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  timeout: 4000,
});

client.interceptors.request.use((config) => {
  const token = localStorage.getItem('askuy_token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

const fallback = async (request, localData) => {
  try {
    const response = await request();
    return response.data;
  } catch {
    return localData;
  }
};

export const authService = {
  login: async (email, password) => fallback(
    () => client.post('/auth/login', { email, password }),
    { user: { ...initialData.currentUser, email }, token: 'mock-jwt-token-askuy' },
  ),
  register: async (data) => fallback(
    () => client.post('/auth/register', data),
    { user: { ...initialData.currentUser, name: data.name, email: data.email, education_level: data.education_level, major: data.major || null }, token: 'mock-jwt-token-askuy' },
  ),
  logout: () => fallback(() => client.post('/auth/logout'), { success: true }),
};

export const dashboardService = { getStats: () => fallback(() => client.get('/dashboard'), initialData.dashboard) };
export const practiceService = {
  getPackages: () => fallback(() => client.get('/practice-packages'), initialData.practicePackages),
  generatePackage: (data) => fallback(() => client.post('/practice-packages', data), { success: true, data }),
};
export const chatService = {
  getSessions: () => fallback(() => client.get('/chat-sessions'), initialData.chatSessions),
  sendMessage: (sessionId, message) => fallback(
    () => client.post(`/chat-sessions/${sessionId}/messages`, { message }),
    { id: Date.now(), role: 'assistant', content: `Mari kita bedah pertanyaanmu: "${message}". Mulai dari konsep dasar, lalu uji dengan contoh singkat sebelum menarik kesimpulan.`, created_at: new Date().toISOString() },
  ),
};
export const focusService = {
  getSessions: () => fallback(() => client.get('/focus-sessions'), initialData.focusSessions),
  getFlashcards: () => fallback(() => client.get('/flashcards'), initialData.flashcards),
  saveSession: (data) => fallback(() => client.post('/focus-sessions', data), { success: true, data }),
};
export const evaluationService = { getEvaluation: (sessionId = 1) => fallback(() => client.get(`/exam-sessions/${sessionId}`), initialData.evaluation) };
