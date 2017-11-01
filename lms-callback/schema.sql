CREATE TABLE "users"
(
  id INTEGER PRIMARY KEY,
  client_user_id VARCHAR(32) NOT NULL
);
CREATE TABLE "videos"
(
  id INTEGER PRIMARY KEY,
  media_content_key VARCHAR(32)
);
CREATE TABLE "progress_relations"
(
  id INTEGER PRIMARY KEY,
  video_id INT NOT NULL,
  user_id INT NOT NULL,
  progress_block_info TEXT DEFAULT '',
  progress_values FLOAT DEFAULt 0,
  start_at INT,
  updated_at INT,
  CONSTRAINT callback_relations_user_id_fk FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT callback_relations_video_id_fk FOREIGN KEY (video_id) REFERENCES videos (id)
);
CREATE UNIQUE INDEX progress_relations_video_id_user_id_uindex ON "progress_relations" (video_id DESC, user_id DESC);
CREATE TABLE "progress_datas"
(
  id INTEGER PRIMARY KEY,
  progress_relation_id INTEGER,
  start_at INT,
  progress_block_info TEXT DEFAULT '',
  playtime INT DEFAULT 0,
  player_id TEXT,
  device_name VARCHAR(255),
  updated_at INT,
  CONSTRAINT progress_datas_progress_relations_id_fk FOREIGN KEY (progress_relation_id) REFERENCES progress_relations (id)
);
