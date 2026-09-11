<?php
/**
 * Koneksi database (mysqli). Setara koneksi.php lama, dibungkus kelas
 * agar bisa diakses lewat Database::conn() dari mana pun.
 */
class Database
{
    private static ?mysqli $conn = null;

    public static function conn(): mysqli
    {
        if (self::$conn === null) {
            $host     = "localhost";
            $user     = "root"; // Default user XAMPP
            $password = "";     // Default password XAMPP kosong
            $db       = "arsip_tikkim";

            $conn = mysqli_connect($host, $user, $password, $db);
            if (!$conn) {
                die("Koneksi gagal: " . mysqli_connect_error());
            }
            self::$conn = $conn;
        }
        return self::$conn;
    }

    /**
     * Prepared statement untuk semua query mutasi (INSERT/UPDATE/DELETE).
     */
    public static function exec(string $sql, string $types = '', array $params = []): bool
    {
        $conn = self::conn();
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return false;
        if ($types !== '' && count($params) > 0) {
            $bind = [$stmt, $types];
            foreach ($params as $i => $p) { $bind[] = &$params[$i]; }
            call_user_func_array('mysqli_stmt_bind_param', $bind);
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    /**
     * Prepared statement untuk SELECT yang butuh binding (mengembalikan mysqli_result|null).
     */
    public static function query(string $sql, string $types = '', array $params = []): ?mysqli_result
    {
        $conn = self::conn();
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) return null;
        if ($types !== '' && count($params) > 0) {
            $bind = [$stmt, $types];
            foreach ($params as $i => $p) { $bind[] = &$params[$i]; }
            call_user_func_array('mysqli_stmt_bind_param', $bind);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result ?: null;
    }

    /** SELECT tanpa parameter (aman untuk query statis, bukan input user). */
    public static function raw(string $sql): mysqli_result|bool
    {
        return mysqli_query(self::conn(), $sql);
    }
}
