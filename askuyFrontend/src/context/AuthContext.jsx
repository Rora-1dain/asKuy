/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useState } from 'react';
import { authService } from '../services/api';
import { initialData } from '../data/initialData';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    const saved = localStorage.getItem('askuy_user');
    return saved ? JSON.parse(saved) : initialData.currentUser;
  });
  const [token, setToken] = useState(() => localStorage.getItem('askuy_token') || 'mock-jwt-token-askuy');

  const login = async (email, password, metadata = {}) => {
    const result = await authService.login(email, password);
    const nextUser = {
      ...initialData.currentUser,
      ...result.user,
      email,
      program: metadata.degree ? `${metadata.degree} - ${metadata.field || 'Informatika'}` : result.user?.program || initialData.currentUser.program,
    };
    const nextToken = result.token || 'mock-jwt-token-askuy';
    setUser(nextUser);
    setToken(nextToken);
    localStorage.setItem('askuy_user', JSON.stringify(nextUser));
    localStorage.setItem('askuy_token', nextToken);
    return { success: true };
  };

  const register = async ({ name, email, password, educationLevel, major }) => {
    const result = await authService.register({
      name,
      email,
      password,
      education_level: educationLevel,
      major: major || null,
    });
    const nextUser = { ...initialData.currentUser, ...result.user, name, email, program: major || educationLevel };
    const nextToken = result.access_token || result.token || 'mock-jwt-token-askuy';
    setUser(nextUser);
    setToken(nextToken);
    localStorage.setItem('askuy_user', JSON.stringify(nextUser));
    localStorage.setItem('askuy_token', nextToken);
    return { success: true };
  };

  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('askuy_user');
    localStorage.removeItem('askuy_token');
  };

  return <AuthContext.Provider value={{ user, token, login, register, logout, isAuthenticated: Boolean(user) }}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const value = useContext(AuthContext);
  if (!value) throw new Error('useAuth must be used within AuthProvider');
  return value;
}
