import { useEffect, useState } from 'react';
import Header from './components/Header';
import { AuthProvider, useAuth } from './context/AuthContext';
import { LanguageProvider, useLanguage } from './context/LanguageContext';
import { ThemeProvider } from './context/ThemeContext';
import BerandaPage from './pages/BerandaPage';
import ChatPage from './pages/ChatPage';
import EvaluasiPage from './pages/EvaluasiPage';
import FokusPage from './pages/FokusPage';
import LoginPage from './pages/LoginPage';
import ModulPage from './pages/ModulPage';

const translations = {
  Beranda: 'Home',
  Evaluasi: 'Evaluation',
  'Modul & Latihan': 'Modules & Practice',
  Fokus: 'Focus',
  Keluar: 'Sign out',
  'Mulai Belajar': 'Start studying',
  'Perbarui evaluasi': 'Update evaluation',
  'IPK saat ini': 'Current GPA',
  'SKS selesai': 'Credits completed',
  'Streak belajar': 'Study streak',
  'RPA sync': 'RPA sync',
  'hari': 'days',
  'Peta materi prioritas': 'Priority topic map',
  'Rekomendasi modul otomatis': 'Automated module recommendations',
  'Lihat semua': 'View all',
  'Tanya AI Tutor': 'Ask AI Tutor',
  'Ruang fokus dan pemulihan': 'Focus and recovery room',
  'Focus room': 'Focus room',
  'Mulai fokus': 'Start focus',
  Jeda: 'Pause',
  Istirahat: 'Break',
  'Rehat panjang': 'Long break',
  'Kartu pengulangan aktif': 'Active recall cards',
  Paham: 'Understood',
  'Tips energi belajar': 'Study energy tip',
  'Modul dan bank soal': 'Modules and question bank',
  'Generate paket latihan': 'Generate practice package',
  Menyiapkan: 'Preparing',
  Diskusikan: 'Discuss',
  'Unduh modul': 'Download module',
  'Cari mata kuliah, topik, atau kode...': 'Search courses, topics, or codes...',
  'Evaluasi hasil belajar': 'Learning evaluation',
  'Unggah nilai': 'Upload grades',
  'Indeks performa terakhir': 'Latest performance index',
  'Peta penguasaan mata kuliah': 'Course mastery map',
  'Langkah rekomendasi': 'Recommended steps',
  'Mulai paket prioritas': 'Start priority package',
  'Belajar dengan penalaran bertahap': 'Learn with step-by-step reasoning',
  'AI sedang menyusun penjelasan...': 'AI is preparing an explanation...',
  Kirim: 'Send',
  'Daftar Akun': 'Create account',
  Masuk: 'Sign in',
  Email: 'Email',
  'Kata sandi': 'Password',
  'Nama lengkap': 'Full name',
  'Sinkronisasi akademik': 'Academic sync',
  Otomatis: 'Automatic',
  Jurusan: 'Major',
  Jenjang: 'Education level',
  'Ruang belajar hari ini sudah memetakan prioritas materi, latihan, dan target akademikmu.': "Your study space has mapped today's priority topics, practice, and academic targets.",
  'Progres menuju target kelulusan': 'Progress toward graduation target',
  'Paket latihan baru berhasil ditambahkan ke antrean RPA.': 'A new practice package has been added to the RPA queue.',
  'Tidak ada modul yang sesuai dengan pencarianmu.': 'No modules match your search.',
  'AI menjelaskan konsep, mengecek logika, dan memberi latihan lanjutan.': 'AI explains concepts, checks reasoning, and provides follow-up practice.',
  'Tanyakan konsep atau kirim soal...': 'Ask about a concept or send a question...',
  'Audio latar untuk sesi tanpa distraksi.': 'Background audio for a distraction-free session.',
  'Klik untuk melihat pertanyaan.': 'Click to reveal the question.',
  'Klik untuk melihat jawaban.': 'Click to reveal the answer.',
  'Gunakan aturan 20-20-20: tiap 20 menit, pandang objek sekitar 6 meter selama 20 detik.': 'Use the 20-20-20 rule: every 20 minutes, look at something 20 feet away for 20 seconds.',
  'Jadwalkan rehat singkat setelah satu sesi.': 'Schedule a short break after each session.',
  'Kenaikan sejak evaluasi sebelumnya': 'Increase since previous evaluation',
  'Menjadi prioritas latihan minggu ini': 'Prioritized for practice this week',
  'Nilai evaluasi:': 'Evaluation score:',
  '1. Kerjakan paket prioritas untuk Kalkulus Lanjut II.': '1. Complete the priority package for Advanced Calculus II.',
  '2. Gunakan AI Tutor saat menemukan langkah yang tidak dipahami.': '2. Ask the AI Tutor about any unclear steps.',
  '3. Ulangi evaluasi setelah tiga sesi fokus.': '3. Repeat the evaluation after three focus sessions.',
  'Berkas diterima. Analisis akan diperbarui setelah sinkronisasi.': 'File received. Analysis will update after synchronization.',
  'RPA Knowledge Archive': 'RPA Knowledge Archive',
  'Mode demo aktif ketika API kampus belum tersambung.': 'Demo mode is active while the campus API is disconnected.',
  'Simpan sesi login di peramban ini': 'Remember me on this browser',
  'Atau autentikasi melalui': 'Or sign in with',
  'Masuk & Sinkronisasi Studi': 'Sign in and sync studies',
  'Buat Akun & Sinkronisasi Studi': 'Create account and sync studies',
};

function TranslationLayer() {
  const { language } = useLanguage();

  useEffect(() => {
    const reverse = Object.fromEntries(Object.entries(translations).map(([id, en]) => [en, id]));
    const dictionary = language === 'en' ? translations : reverse;
    const translateValue = (value) => {
      const trimmed = value.trim();
      return dictionary[trimmed] ? value.replace(trimmed, dictionary[trimmed]) : value;
    };
    const translateElement = (element) => {
      if (element.nodeType === Node.TEXT_NODE) {
        if (!element.parentElement?.closest('script, style')) {
          const currentValue = element.nodeValue || '';
          const translatedValue = translateValue(currentValue);
          if (translatedValue !== currentValue) element.nodeValue = translatedValue;
        }
        return;
      }
      if (element.nodeType !== Node.ELEMENT_NODE) return;
      ['placeholder', 'aria-label', 'title'].forEach((attribute) => {
        const value = element.getAttribute(attribute);
        if (value) element.setAttribute(attribute, translateValue(value));
      });
      const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
      while (walker.nextNode()) translateElement(walker.currentNode);
    };
    translateElement(document.body);
    const observer = new MutationObserver((changes) => {
      changes.forEach((change) => change.addedNodes.forEach(translateElement));
    });
    observer.observe(document.body, { childList: true, characterData: true, subtree: true });
    return () => observer.disconnect();
  }, [language]);

  return null;
}

function Shell() {
  const { isAuthenticated } = useAuth();
  const [activeTab, setActiveTab] = useState(isAuthenticated ? 'beranda' : 'login');
  const navigate = (tab) => { setActiveTab(tab); window.scrollTo({ top: 0, behavior: 'smooth' }); };
  const pages = { beranda: <BerandaPage onNavigate={navigate} />, chat: <ChatPage />, evaluasi: <EvaluasiPage onNavigate={navigate} />, modul: <ModulPage onNavigate={navigate} />, fokus: <FokusPage /> };
  return <div className="min-h-screen bg-[#f8fafc] text-[#00022e] dark:bg-[#00022e] dark:text-white"><TranslationLayer />{activeTab !== 'login' && <Header activeTab={activeTab} onTabChange={navigate} />}<main>{activeTab === 'login' ? <LoginPage onLoginSuccess={() => navigate('beranda')} /> : pages[activeTab]}</main></div>;
}

export default function App() { return <ThemeProvider><LanguageProvider><AuthProvider><Shell /></AuthProvider></LanguageProvider></ThemeProvider>; }