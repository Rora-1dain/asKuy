import { useState } from 'react';
import {
  ArrowRight,
  Building2,
  ChevronDown,
  CircleHelp,
  Eye,
  EyeOff,
  GraduationCap,
  LockKeyhole,
  Mail,
  Moon,
  ShieldCheck,
  SlidersHorizontal,
  Sun,
  UserRound,
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { useLanguage } from '../context/LanguageContext';

const degreeOptions = [
  { value: 'sma', label: 'SMA' },
  { value: 'smk', label: 'SMK' },
  { value: 'sarjana', label: 'Sarjana (S1 / D4)' },
  { value: 'magister', label: 'Magister (S2)' },
  { value: 'prefer_not_to_say', label: 'Prefer not to say' },
];

const majorOptions = [
  'Akuntansi',
  'Arsitektur',
  'Bahasa dan Sastra',
  'Bisnis Digital',
  'Desain Komunikasi Visual',
  'Ekonomi',
  'Farmasi',
  'Hukum',
  'Ilmu Komunikasi',
  'Kedokteran',
  'Manajemen',
  'Pendidikan',
  'Perhotelan dan Pariwisata',
  'Sains dan Rekayasa Data',
  'Sistem Informasi dan RPA',
  'Teknik Elektro',
  'Teknik Industri',
  'Teknik Informatika dan AI',
  'Teknik Mesin',
  'Teknik Sipil',
  'Tata Boga',
  'Tata Busana',
  'Usaha Layanan Pariwisata',
  'Lainnya',
];

const fieldIsRequired = (degree) => ['smk', 'sarjana', 'magister'].includes(degree);

function SelectField({ id, value, onChange, children, disabled = false }) {
  return <div className="relative"><select id={id} value={value} onChange={onChange} disabled={disabled} className="login-select h-10 w-full appearance-none rounded-md border border-slate-300 bg-transparent px-3 pr-9 text-sm font-semibold outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/25">{children}</select><ChevronDown className="pointer-events-none absolute right-3 top-3 text-slate-400" size={16} /></div>;
}

export default function LoginPage({ onLoginSuccess }) {
  const { login, register } = useAuth();
  const { isDark, toggleTheme } = useTheme();
  const { language, setLanguage } = useLanguage();
  const [mode, setMode] = useState('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [degree, setDegree] = useState('sarjana');
  const [major, setMajor] = useState('Teknik Informatika dan AI');
  const [rememberMe, setRememberMe] = useState(true);
  const [showPassword, setShowPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const requiresMajor = fieldIsRequired(degree);

  const changeMode = (nextMode) => {
    setMode(nextMode);
    setError('');
  };

  const changeDegree = (event) => {
    const nextDegree = event.target.value;
    setDegree(nextDegree);
    if (!fieldIsRequired(nextDegree)) setMajor('');
  };

  const submit = async (event) => {
    event.preventDefault();
    setError('');
    if (requiresMajor && !major) {
      setError('Jurusan wajib dipilih untuk jenjang SMK, S1/D4, dan S2.');
      return;
    }
    setSubmitting(true);
    try {
      if (mode === 'login') await login(email, password, { degree, field: major });
      else await register({ name, email, password, educationLevel: degree, major });
      onLoginSuccess();
    } catch (requestError) {
      setError(requestError.message || 'Permintaan tidak dapat diproses. Coba lagi.');
    } finally {
      setSubmitting(false);
    }
  };

  const heading = mode === 'login' ? 'Masuk ke asKuy' : 'Daftar akun asKuy';
  const description = mode === 'login'
    ? 'Masuk untuk mengakses AI Tutor & Automasi Belajar terintegrasi.'
    : 'Buat akun untuk menyimpan progres dan sinkronisasi akademikmu.';
  const submitLabel = mode === 'login' ? 'Masuk & Sinkronisasi Studi' : 'Buat Akun & Sinkronisasi Studi';

  return <div className="min-h-screen bg-white text-[#00022e] transition-colors dark:bg-[#00022e] dark:text-white">
    <header className="flex h-16 items-center justify-between border-b border-slate-200 px-4 sm:px-8 dark:border-white/15"><div className="flex items-center gap-3"><span className="grid h-8 w-8 place-items-center rounded-lg bg-[#00022e] text-xs font-extrabold text-white dark:bg-white dark:text-[#00022e]">aK</span><span className="text-lg font-extrabold tracking-tight">asKuy</span><span className="rounded border border-slate-300 px-2 py-0.5 font-mono text-[10px] font-semibold text-slate-600 dark:border-white/20 dark:text-slate-300">v2.4 Akademik</span></div><div className="flex items-center gap-3 text-xs font-semibold"><span className="hidden items-center gap-1.5 text-slate-600 md:flex dark:text-slate-300"><ShieldCheck size={15} />ISO/IEC 27001</span><span className="hidden items-center gap-1.5 sm:flex"><CircleHelp size={15} />Bantuan</span><button type="button" onClick={toggleTheme} className="flex h-9 items-center gap-1.5 rounded-lg border border-slate-300 px-3 dark:border-white/20" aria-label="Ubah tema">{isDark ? <Sun size={16} /> : <Moon size={16} />}<span className="hidden sm:inline">{isDark ? 'Light' : 'Dark'}</span></button><span className="hidden rounded-lg border border-slate-300 p-0.5 sm:flex dark:border-white/20"><button type="button" onClick={() => setLanguage('id')} className={language === 'id' ? 'rounded bg-[#00022e] px-2 py-1 text-white dark:bg-white dark:text-[#00022e]' : 'px-2 py-1 text-slate-500 dark:text-slate-300'}>ID</button><button type="button" onClick={() => setLanguage('en')} className={language === 'en' ? 'rounded bg-[#00022e] px-2 py-1 text-white dark:bg-white dark:text-[#00022e]' : 'px-2 py-1 text-slate-500 dark:text-slate-300'}>EN</button></span></div></header>
    <main className="flex min-h-[calc(100vh-4rem)] flex-col items-center px-4 py-12 sm:py-14"><p className="mb-5 flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium dark:border-white/20"><span className="h-2 w-2 rounded-full bg-[#00022e] dark:bg-white" />Pusat Autentikasi Mahasiswa & Peneliti Kampus</p><section className="w-full max-w-[42rem] rounded-2xl border border-slate-300 bg-white p-5 shadow-sm sm:p-8 dark:border-white/20 dark:bg-[#050838] dark:shadow-none"><div className="mx-auto max-w-[35rem]"><div className="text-center"><span className="mx-auto grid h-14 w-14 place-items-center rounded-xl border border-slate-300 bg-slate-50 text-[#00022e] dark:border-white/20 dark:bg-white/10 dark:text-white"><GraduationCap size={25} /></span><h1 className="mt-5 text-3xl font-extrabold tracking-tight">{heading}</h1><p className="mt-2 text-sm text-slate-500 dark:text-slate-300">{description}</p></div>
      <div className="mt-8 grid grid-cols-2 rounded-lg border border-slate-300 p-1 text-sm font-bold dark:border-white/20"><button type="button" onClick={() => changeMode('login')} className={mode === 'login' ? 'rounded-md bg-[#00022e] py-2 text-white dark:bg-white dark:text-[#00022e]' : 'py-2 text-slate-500 dark:text-slate-300'}>Masuk</button><button type="button" onClick={() => changeMode('register')} className={mode === 'register' ? 'rounded-md bg-[#00022e] py-2 text-white dark:bg-white dark:text-[#00022e]' : 'py-2 text-slate-500 dark:text-slate-300'}>Daftar Akun</button></div>
      <form onSubmit={submit} className="mt-6 space-y-4">{mode === 'register' && <label className="block text-xs font-extrabold uppercase tracking-wide">Nama lengkap<span className="relative mt-2 block"><UserRound className="pointer-events-none absolute left-3 top-3 text-slate-400" size={17} /><input type="text" required value={name} onChange={(event) => setName(event.target.value)} placeholder="Nama sesuai identitas" className="h-11 w-full rounded-lg border border-slate-300 bg-transparent pl-10 pr-3 text-sm outline-none transition focus:border-[#00022e] focus:ring-1 focus:ring-[#00022e] dark:border-white/30 dark:focus:border-white dark:focus:ring-white" /></span></label>}
        <label className="block text-xs font-extrabold uppercase tracking-wide">Email<span className="relative mt-2 block"><Mail className="pointer-events-none absolute left-3 top-3 text-slate-400" size={17} /><input type="email" required value={email} onChange={(event) => setEmail(event.target.value)} placeholder="nama@mahasiswa.univ.ac.id" className="h-11 w-full rounded-lg border border-slate-300 bg-transparent pl-10 pr-3 text-sm outline-none transition focus:border-[#00022e] focus:ring-1 focus:ring-[#00022e] dark:border-white/30 dark:focus:border-white dark:focus:ring-white" /></span></label>
        <label className="block text-xs font-extrabold uppercase tracking-wide">Kata sandi<span className="relative mt-2 block"><LockKeyhole className="pointer-events-none absolute left-3 top-3 text-slate-400" size={17} /><input type={showPassword ? 'text' : 'password'} required minLength="8" value={password} onChange={(event) => setPassword(event.target.value)} placeholder="Minimal 8 karakter terverifikasi" className="h-11 w-full rounded-lg border border-slate-300 bg-transparent pl-10 pr-10 text-sm outline-none transition focus:border-[#00022e] focus:ring-1 focus:ring-[#00022e] dark:border-white/30 dark:focus:border-white dark:focus:ring-white" /><button type="button" onClick={() => setShowPassword((value) => !value)} className="absolute right-3 top-3 text-slate-400" aria-label={showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'}>{showPassword ? <EyeOff size={17} /> : <Eye size={17} />}</button></span></label>
        <section className="rounded-lg border border-slate-300 p-4 dark:border-white/20"><div className="mb-3 flex items-center justify-between"><p className="text-xs font-extrabold uppercase tracking-wide">Sinkronisasi akademik</p><span className="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-300"><SlidersHorizontal size={14} />Otomatis</span></div><div className="grid gap-3 sm:grid-cols-2"><label className="sr-only" htmlFor="degree">Jenjang</label><SelectField id="degree" value={degree} onChange={changeDegree}>{degreeOptions.map((item) => <option value={item.value} key={item.value}>{item.label}</option>)}</SelectField><label className="sr-only" htmlFor="major">Jurusan</label><SelectField id="major" value={major} onChange={(event) => setMajor(event.target.value)} disabled={!requiresMajor}><option value="">{requiresMajor ? 'Pilih jurusan' : 'Tidak wajib untuk jenjang ini'}</option>{majorOptions.map((item) => <option key={item}>{item}</option>)}</SelectField></div><p className="mt-2 text-[11px] text-slate-500 dark:text-slate-400">{requiresMajor ? 'Jurusan wajib dipilih untuk jenjang ini.' : 'Jurusan opsional untuk SMA dan Prefer not to say.'}</p></section>
        {error && <p role="alert" className="rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-xs font-medium text-[#00022e] dark:border-white/20 dark:bg-white/10 dark:text-white">{error}</p>}
        <label className="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300"><input type="checkbox" checked={rememberMe} onChange={(event) => setRememberMe(event.target.checked)} className="h-4 w-4 accent-[#00022e] dark:accent-white" />Simpan sesi login di peramban ini</label><button disabled={submitting} className="flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#00022e] text-sm font-extrabold text-white transition hover:bg-[#050838] disabled:opacity-60 dark:bg-white dark:text-[#00022e] dark:hover:bg-slate-200">{submitting ? 'Memverifikasi...' : submitLabel}<ArrowRight size={17} /></button></form>
      <div className="my-7 flex items-center gap-3 text-[11px] font-medium uppercase tracking-wider text-slate-500 before:h-px before:flex-1 before:bg-slate-300 after:h-px after:flex-1 after:bg-slate-300 dark:text-slate-400 dark:before:bg-white/15 dark:after:bg-white/15">Atau autentikasi melalui</div><div className="grid grid-cols-2 gap-3"><button type="button" className="flex h-11 items-center justify-center gap-2 rounded-lg border border-slate-300 text-xs font-bold dark:border-white/30"><Building2 size={16} />SSO Kampus ID</button><button type="button" className="h-11 rounded-lg border border-slate-300 text-xs font-bold dark:border-white/30">Google Edu</button></div><div className="mt-7 flex items-center justify-between border-t border-slate-200 pt-4 text-[11px] text-slate-500 dark:border-white/15 dark:text-slate-400"><span>RPA Studio & AI Lab v2</span><span>Koneksi SSL 256-bit</span></div></div></section><footer className="mt-8 max-w-md text-center text-xs leading-5 text-slate-500 dark:text-slate-400">Didukung oleh Kurikulum Kampus Merdeka & Standar Dikti RI - Keamanan Terenkripsi<br /><span className="font-semibold">Kebijakan Privasi Riset - Integritas Akademik - Server Kampus Aktif</span></footer></main>
  </div>;
}