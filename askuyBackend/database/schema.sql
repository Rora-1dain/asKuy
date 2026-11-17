-- ENUM
CREATE TYPE user_role AS ENUM ('student', 'instructor', 'admin');
CREATE TYPE question_difficulty AS ENUM ('mudah', 'sedang', 'hots');
CREATE TYPE package_status AS ENUM ('ready', 'in_progress', 'completed');
CREATE TYPE weakness_status AS ENUM ('critical', 'improving', 'mastered');
CREATE TYPE focus_session_type AS ENUM ('pomodoro', 'short_break', 'long_break');

-- 1. users
CREATE TABLE users (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role user_role NOT NULL DEFAULT 'student',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 2. subjects
CREATE TABLE subjects (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  code VARCHAR(50) NOT NULL UNIQUE,
  semester INTEGER NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 3. questions
CREATE TABLE questions (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
  topic_name VARCHAR(255) NOT NULL,
  difficulty question_difficulty NOT NULL DEFAULT 'sedang',
  question_text TEXT NOT NULL,
  explanation TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE question_options (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  question_id BIGINT NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
  option_label VARCHAR(5) NOT NULL,
  option_text TEXT NOT NULL,
  is_correct BOOLEAN NOT NULL DEFAULT FALSE
);

-- 4. practice_packages
CREATE TABLE practice_packages (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  total_questions INTEGER NOT NULL DEFAULT 0,
  status package_status NOT NULL DEFAULT 'ready',
  pdf_path VARCHAR(500),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 5. exam_sessions
CREATE TABLE exam_sessions (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  package_id BIGINT REFERENCES practice_packages(id) ON DELETE SET NULL,
  score DECIMAL(5,2) NOT NULL DEFAULT 0,
  accuracy_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
  duration_seconds INTEGER NOT NULL DEFAULT 0,
  grade_category VARCHAR(100),
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE exam_answers (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  exam_session_id BIGINT NOT NULL REFERENCES exam_sessions(id) ON DELETE CASCADE,
  question_id BIGINT NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
  selected_option_id BIGINT REFERENCES question_options(id) ON DELETE SET NULL,
  is_correct BOOLEAN NOT NULL DEFAULT FALSE
);

-- 6. ai_weakness_mappings
CREATE TABLE ai_weakness_mappings (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
  weak_topic VARCHAR(255) NOT NULL,
  mastery_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
  status weakness_status NOT NULL DEFAULT 'critical',
  last_evaluated_at TIMESTAMPTZ
);

-- 7. flashcards
CREATE TABLE flashcards (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  subject_id BIGINT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
  front_content TEXT NOT NULL,
  back_content TEXT NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 8. focus_sessions
CREATE TABLE focus_sessions (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  duration_minutes INTEGER NOT NULL,
  ambient_sound_used VARCHAR(100),
  session_type focus_session_type NOT NULL DEFAULT 'pomodoro',
  completed_at TIMESTAMPTZ
);

-- Index FK
CREATE INDEX idx_questions_subject ON questions(subject_id);
CREATE INDEX idx_options_question ON question_options(question_id);
CREATE INDEX idx_packages_user ON practice_packages(user_id);
CREATE INDEX idx_sessions_user ON exam_sessions(user_id);
CREATE INDEX idx_answers_session ON exam_answers(exam_session_id);
CREATE INDEX idx_weakness_user ON ai_weakness_mappings(user_id);
CREATE INDEX idx_flashcards_subject ON flashcards(subject_id);
CREATE INDEX idx_focus_user ON focus_sessions(user_id);

-- Auto update updated_at
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_subjects_updated BEFORE UPDATE ON subjects FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_questions_updated BEFORE UPDATE ON questions FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_packages_updated BEFORE UPDATE ON practice_packages FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_sessions_updated BEFORE UPDATE ON exam_sessions FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_flashcards_updated BEFORE UPDATE ON flashcards FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- users: profil akademik + Google login
ALTER TABLE users
  ADD COLUMN education_level VARCHAR(30),
  ADD COLUMN major VARCHAR(150),
  ADD COLUMN study_goal VARCHAR(255),
  ADD COLUMN google_id VARCHAR(100) UNIQUE,
  ADD COLUMN avatar_url VARCHAR(500),
  ADD COLUMN study_streak INTEGER NOT NULL DEFAULT 0,
  ADD COLUMN last_study_date DATE,
  ADD CONSTRAINT users_education_level_chk
    CHECK (education_level IN ('sma_smk','sarjana','pascasarjana') OR education_level IS NULL);
ALTER TABLE users ALTER COLUMN password DROP NOT NULL; -- akun Google tanpa password

-- practice_packages: status ikut alur UiPath
CREATE TYPE package_status_new AS ENUM ('pending','processing','completed','failed');
ALTER TABLE practice_packages ALTER COLUMN status DROP DEFAULT;
ALTER TABLE practice_packages ALTER COLUMN status TYPE package_status_new
  USING (CASE status::text
           WHEN 'in_progress' THEN 'processing'
           ELSE 'completed' END)::package_status_new;
ALTER TABLE practice_packages ALTER COLUMN status SET DEFAULT 'pending';
DROP TYPE package_status;
ALTER TYPE package_status_new RENAME TO package_status;
ALTER TABLE practice_packages
  ADD COLUMN topic_name VARCHAR(255),
  ADD COLUMN difficulty question_difficulty NOT NULL DEFAULT 'sedang',
  ADD COLUMN error_message TEXT,
  ADD COLUMN completed_at TIMESTAMPTZ;

-- focus_sessions: stress tracker
ALTER TABLE focus_sessions
  ADD COLUMN stress_level SMALLINT CHECK (stress_level BETWEEN 1 AND 5);

-- chat AI Tutor
CREATE TYPE chat_role AS ENUM ('user','assistant');
CREATE TABLE chat_sessions (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  subject_id BIGINT REFERENCES subjects(id) ON DELETE SET NULL,
  title VARCHAR(255) NOT NULL DEFAULT 'Sesi baru',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE TABLE chat_messages (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  chat_session_id BIGINT NOT NULL REFERENCES chat_sessions(id) ON DELETE CASCADE,
  role chat_role NOT NULL,
  content TEXT NOT NULL,
  image_path VARCHAR(500),
  metadata JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- upload nilai / kuis
CREATE TYPE job_status AS ENUM ('pending','processing','completed','failed');
CREATE TABLE grade_uploads (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  subject_id BIGINT REFERENCES subjects(id) ON DELETE SET NULL,
  original_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_type VARCHAR(10) NOT NULL,
  status job_status NOT NULL DEFAULT 'pending',
  analysis_result JSONB,
  error_message TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- progres flashcard per user
CREATE TYPE flashcard_status AS ENUM ('not_memorized','memorized');
CREATE TABLE flashcard_progress (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  flashcard_id BIGINT NOT NULL REFERENCES flashcards(id) ON DELETE CASCADE,
  status flashcard_status NOT NULL DEFAULT 'not_memorized',
  last_reviewed_at TIMESTAMPTZ,
  UNIQUE (user_id, flashcard_id)
);

-- tabel wajib Laravel (kita nggak pakai migrate)
CREATE TABLE personal_access_tokens (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tokenable_type VARCHAR(255) NOT NULL,
  tokenable_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  abilities TEXT,
  last_used_at TIMESTAMPTZ,
  expires_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ,
  updated_at TIMESTAMPTZ
);
CREATE INDEX idx_pat_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);

CREATE TABLE jobs (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  queue VARCHAR(255) NOT NULL,
  payload TEXT NOT NULL,
  attempts SMALLINT NOT NULL,
  reserved_at INTEGER,
  available_at INTEGER NOT NULL,
  created_at INTEGER NOT NULL
);
CREATE INDEX idx_jobs_queue ON jobs(queue);
CREATE TABLE failed_jobs (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  uuid VARCHAR(255) NOT NULL UNIQUE,
  connection TEXT NOT NULL,
  queue TEXT NOT NULL,
  payload TEXT NOT NULL,
  exception TEXT NOT NULL,
  failed_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- index + trigger updated_at
CREATE INDEX idx_chat_sessions_user ON chat_sessions(user_id);
CREATE INDEX idx_chat_messages_session ON chat_messages(chat_session_id);
CREATE INDEX idx_uploads_user ON grade_uploads(user_id);
CREATE INDEX idx_fcprogress_user ON flashcard_progress(user_id);
CREATE TRIGGER trg_chat_sessions_updated BEFORE UPDATE ON chat_sessions FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_uploads_updated BEFORE UPDATE ON grade_uploads FOR EACH ROW EXECUTE FUNCTION set_updated_at();

ALTER TABLE users ADD COLUMN remember_token VARCHAR(100);