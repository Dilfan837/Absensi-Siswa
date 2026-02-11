-- Check if siswa1 user exists and get id
SELECT id_user, username, id_role FROM users WHERE username = 'siswa1';

-- Check if siswa data exists for this user
SELECT * FROM siswa WHERE id_user IN (SELECT id_user FROM users WHERE username = 'siswa1');

-- If siswa doesn't exist, create it (adjust id_kelas if needed)
-- First check available kelas
SELECT * FROM kelas LIMIT 1;

-- Insert siswa data (run this if siswa is missing)
-- Replace <USER_ID> with the actual id_user from first query
-- Replace <KELAS_ID> with an existing id_kelas from kelas table
/*
INSERT INTO siswa (id_user, nis, nama_siswa, id_kelas, jenis_kelamin, status_aktif, created_at, updated_at)
VALUES (<USER_ID>, '123456789', 'Siswa Demo', <KELAS_ID>, 'L', 1, NOW(), NOW());
*/
