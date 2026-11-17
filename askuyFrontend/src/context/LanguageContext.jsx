/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useEffect, useState } from 'react';

const LanguageContext = createContext(null);

export function LanguageProvider({ children }) {
  const [language, setLanguage] = useState(() => localStorage.getItem('askuy_language') || 'id');

  useEffect(() => {
    document.documentElement.lang = language;
    localStorage.setItem('askuy_language', language);
  }, [language]);

  return <LanguageContext.Provider value={{ language, setLanguage, isEnglish: language === 'en' }}>{children}</LanguageContext.Provider>;
}

export function useLanguage() {
  const value = useContext(LanguageContext);
  if (!value) throw new Error('useLanguage must be used within LanguageProvider');
  return value;
}