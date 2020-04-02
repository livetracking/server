BEGIN TRANSACTION;
CREATE TABLE "users" (
	`id`                       INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`username`                 TEXT NOT NULL UNIQUE,
	`password_hash`            TEXT NOT NULL
);
COMMIT;
