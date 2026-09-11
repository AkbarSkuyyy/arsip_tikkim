<?php
/**
 * Model tabel users. Setara query di login.php, register.php, edit_user.php,
 * reset_password.php, hapus_user.php, pengaturan.php.
 */
class User
{
    public static function find(int $id): ?array
    {
        $r = Database::query("SELECT * FROM users WHERE id = ?", 'i', [$id]);
        $row = $r ? mysqli_fetch_assoc($r) : null;
        return $row ?: null;
    }

    public static function findByLogin(string $userInput): ?array
    {
        $r = Database::query("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1", 'ss', [$userInput, $userInput]);
        $row = $r ? mysqli_fetch_assoc($r) : null;
        return $row ?: null;
    }

    public static function existsByEmailOrUsername(string $email, string $username, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $r = Database::query("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?", 'ssi', [$email, $username, $excludeId]);
        } else {
            $r = Database::query("SELECT id FROM users WHERE email = ? OR username = ?", 'ss', [$email, $username]);
        }
        return $r && mysqli_num_rows($r) > 0;
    }

    public static function usernameTaken(string $username, int $excludeId): bool
    {
        $r = Database::query("SELECT id FROM users WHERE username = ? AND id != ?", 'si', [$username, $excludeId]);
        return $r && mysqli_num_rows($r) > 0;
    }

    public static function create(array $data): bool
    {
        return Database::exec(
            "INSERT INTO users (nama, username, jabatan, email, password, role) VALUES (?, ?, ?, ?, ?, ?)",
            'ssssss',
            [$data['nama'], $data['username'], $data['jabatan'], $data['email'], $data['password'], $data['role']]
        );
    }

    public static function update(int $id, array $data): bool
    {
        return Database::exec(
            "UPDATE users SET nama = ?, username = ?, jabatan = ?, email = ?, role = ? WHERE id = ?",
            'sssssi',
            [$data['nama'], $data['username'], $data['jabatan'], $data['email'], $data['role'], $id]
        );
    }

    public static function updatePassword(int $id, string $hashedPassword): bool
    {
        return Database::exec("UPDATE users SET password = ? WHERE id = ?", 'si', [$hashedPassword, $id]);
    }

    public static function delete(int $id): bool
    {
        return Database::exec("DELETE FROM users WHERE id = ?", 'i', [$id]);
    }

    public static function all(): array
    {
        $r = Database::raw("SELECT * FROM users ORDER BY id ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function count(): int
    {
        $r = Database::raw("SELECT COUNT(*) as total FROM users");
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function countAdmin(): int
    {
        $r = Database::raw("SELECT COUNT(*) as total FROM users WHERE role='admin'");
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }
}
