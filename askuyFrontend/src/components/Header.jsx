import { Bell, BookOpen, BrainCircuit, House, Languages, LogOut, Moon, PlayCircle, Sun } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useLanguage } from '../context/LanguageContext';
import { useTheme } from '../context/ThemeContext';

const items = [
  ['beranda', 'Beranda', House], ['chat', 'AI Tutor', BrainCircuit], ['evaluasi', 'Evaluasi', BookOpen], ['modul', 'Modul & Latihan', BookOpen], ['fokus', 'Fokus', PlayCircle],
];

export default function Header({ activeTab, onTabChange }) {
  const { user, logout } = useAuth();
  const { language, setLanguage } = useLanguage();
  const { isDark, toggleTheme } = useTheme();
  return <header className="fixed inset-x-0 top-0 z-50 border-b border-slate-200/80 bg-white/95 text-[#00022e] backdrop-blur dark:border-white/10 dark:bg-[#050838]/95 dark:text-white">
    <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 md:px-8">
      <button onClick={() => onTabChange('beranda')} className="flex shrink-0 items-center gap-2 text-lg font-extrabold tracking-tight"><span className="grid h-8 w-8 place-items-center rounded-lg bg-[#00022e] text-sm text-white dark:bg-white dark:text-[#00022e]">aK</span>asKuy</button>
      <nav className="hidden items-center gap-1 md:flex">{items.map(([id, label, Icon]) => <button key={id} onClick={() => onTabChange(id)} className={`flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-semibold transition ${activeTab === id ? 'bg-slate-100 text-[#00022e] dark:bg-white/15 dark:text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10'}`}><Icon size={16} />{label}</button>)}</nav>
      <div className="flex items-center gap-2"><button onClick={toggleTheme} aria-label="Ubah tema" className="grid h-9 w-9 place-items-center rounded-md border border-slate-200 text-[#00022e] hover:bg-slate-100 dark:border-white/15 dark:text-white dark:hover:bg-white/10">{isDark ? <Sun size={17} /> : <Moon size={17} />}</button><span className="flex items-center rounded-md border border-slate-200 p-0.5 text-xs font-bold dark:border-white/15"><Languages size={14} className="mx-1 text-slate-500 dark:text-slate-300" /><button onClick={() => setLanguage('id')} className={language === 'id' ? 'rounded bg-[#00022e] px-2 py-1 text-white dark:bg-white dark:text-[#00022e]' : 'px-2 py-1 text-slate-500 dark:text-slate-300'}>ID</button><button onClick={() => setLanguage('en')} className={language === 'en' ? 'rounded bg-[#00022e] px-2 py-1 text-white dark:bg-white dark:text-[#00022e]' : 'px-2 py-1 text-slate-500 dark:text-slate-300'}>EN</button></span><button aria-label="Notifikasi" className="relative hidden h-9 w-9 place-items-center rounded-md border border-slate-200 text-[#00022e] sm:grid dark:border-white/15 dark:text-white"><Bell size={17} /><span className="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-[#00022e] dark:bg-white" /></button><div className="hidden text-right lg:block"><p className="text-xs font-bold">{user?.name}</p><p className="text-[10px] text-slate-500 dark:text-slate-300">{user?.program}</p></div><button onClick={() => { logout(); onTabChange('login'); }} aria-label="Keluar" className="grid h-9 w-9 place-items-center rounded-md bg-[#00022e] text-white hover:bg-[#050838] dark:bg-white dark:text-[#00022e]"><LogOut size={17} /></button></div>
    </div>
    <nav className="flex h-10 items-center gap-1 overflow-x-auto border-t border-slate-200 px-3 md:hidden dark:border-white/10">{items.map(([id, label, Icon]) => <button key={id} onClick={() => onTabChange(id)} className={`flex shrink-0 items-center gap-1 rounded px-2 py-1 text-xs font-semibold ${activeTab === id ? 'bg-slate-100 text-[#00022e] dark:bg-white/15 dark:text-white' : 'text-slate-500 dark:text-slate-300'}`}><Icon size={14} />{label}</button>)}</nav>
  </header>;
}