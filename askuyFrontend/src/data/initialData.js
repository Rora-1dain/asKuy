export const initialData = {
  currentUser: {
    id: 1,
    name: 'Dimas Arya',
    email: 'dimas.arya@kampus.ac.id',
    program: 'S1 Informatika',
    semester: 4,
    targetGpa: 3.9,
  },
  dashboard: {
    stats: { currentGpa: '3.84', gpaDiff: '+0.12', creditsDone: 82, creditsTarget: 100, streakDays: 14, totalHours: 98, lastSyncText: '10 menit lalu' },
    rpaModules: [
      { id: 1, course: 'Kalkulus Multivariabel', title: 'Latihan turunan orde tinggi dan solusi parsial', totalQuestions: 18, tag: 'UiPath RPA Sync', difficulty: 'HOTS' },
      { id: 2, course: 'Algoritma dan Kompleksitas', title: 'Mastery drill Master Theorem dan O(n log n)', totalQuestions: 15, tag: 'Kurasi AI Tutor', difficulty: 'Sedang' },
      { id: 3, course: 'Basis Data Terdistribusi', title: 'Bedah dependensi fungsional dari 3NF ke BCNF', totalQuestions: 22, tag: 'Silabus Dikti', difficulty: 'HOTS' },
    ],
    weaknesses: [
      { id: 1, course: 'Kalkulus Lanjut II', topic: 'Integral parsial dan LIATE', mastery: 42, severity: 'Tinggi' },
      { id: 2, course: 'Algoritma Pemrograman', topic: 'Rekursi dan memoization', mastery: 58, severity: 'Sedang' },
      { id: 3, course: 'Sains Data dan AI', topic: 'Gradient descent', mastery: 69, severity: 'Aman' },
    ],
  },
  practicePackages: [
    { id: 1, title: 'Paket drill integral parsial', subject: 'Kalkulus Lanjut II', totalQuestions: 18, size: '2.4 MB PDF', difficulty: 'HOTS' },
    { id: 2, title: 'Latihan Master Theorem', subject: 'Algoritma dan Kompleksitas', totalQuestions: 15, size: '1.8 MB PDF', difficulty: 'Sedang' },
    { id: 3, title: 'Bank soal normalisasi relasional', subject: 'Sistem Basis Data', totalQuestions: 20, size: '2.1 MB PDF', difficulty: 'Menengah' },
  ],
  chatSessions: [],
  focusSessions: { todayMinutes: 100, dailyTargetMinutes: 150 },
  flashcards: [
    { id: 1, subject: 'Kalkulus Lanjut II', topic: 'Teknik Integrasi', question: 'Apa urutan prioritas memilih variabel u pada integral parsial?', answer: 'Gunakan LIATE: Logarithmic, Inverse trigonometric, Algebraic, Trigonometric, lalu Exponential.' },
    { id: 2, subject: 'Basis Data', topic: 'Normalisasi', question: 'Kapan relasi memenuhi BCNF?', answer: 'Setiap determinan pada dependensi fungsional nontrivial harus merupakan super key.' },
    { id: 3, subject: 'Algoritma', topic: 'Teorema Master', question: 'Apa yang dibandingkan pada Teorema Master?', answer: 'Bandingkan f(n) dengan n pangkat log basis b dari a.' },
  ],
  evaluation: { score: 82, overallScore: 82 },
};