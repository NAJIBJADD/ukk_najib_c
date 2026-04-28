<?php
class User {
    private $db;
    private $id;
    private $username;
    private $nama;
    private $role;
    private $rayon;
    private $rombel;
    private $nis;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $this->id = $user['id'];
            $this->username = $user['username'];
            $this->nama = $user['nama_lengkap'];
            $this->role = $user['role'];
            $this->rayon = $user['rayon'];
            $this->rombel = $user['rombel'];
            $this->nis = $user['nis'];
            
            $log = new Log();
            $log->add($this->id, 'LOGIN', 'Berhasil login');
            return true;
        }
        return false;
    }
    
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createUser($data) {
        // Cek duplikat username
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        if ($stmt->fetch()) {
            return 'duplicate';
        }
        
        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, password, nama_lengkap, role, rayon, rombel, nis) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['nama_lengkap'],
                $data['role'],
                $data['rayon'] ?? null,
                $data['rombel'] ?? null,
                $data['nis'] ?? null
            ]);
            return $result;
        } catch (PDOException $e) {
            error_log("Create user exception: " . $e->getMessage());
            return false;
        }
    }

    // --- METHOD UPDATE YANG DITAMBAHKAN ---
    public function updateUser($data) {
        try {
            // Jika password diisi, sertakan dalam query update
            if (!empty($data['password'])) {
                $sql = "UPDATE users SET 
                        username = ?, 
                        password = ?, 
                        nama_lengkap = ?, 
                        role = ?, 
                        rayon = ?, 
                        rombel = ?, 
                        nis = ? 
                        WHERE id = ?";
                $params = [
                    $data['username'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $data['nama_lengkap'],
                    $data['role'],
                    $data['rayon'],
                    $data['rombel'],
                    $data['nis'],
                    $data['id']
                ];
            } else {
                // Jika password kosong, update kolom lainnya saja
                $sql = "UPDATE users SET 
                        username = ?, 
                        nama_lengkap = ?, 
                        role = ?, 
                        rayon = ?, 
                        rombel = ?, 
                        nis = ? 
                        WHERE id = ?";
                $params = [
                    $data['username'],
                    $data['nama_lengkap'],
                    $data['role'],
                    $data['rayon'],
                    $data['rombel'],
                    $data['nis'],
                    $data['id']
                ];
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Update user exception: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteUser($id, $currentUserId) {
        if ($id == $currentUserId) return false;
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Getter
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getNama() { return $this->nama; }
    public function getRole() { return $this->role; }
    public function getRayon() { return $this->rayon; }
    public function getRombel() { return $this->rombel; }
    public function getNis() { return $this->nis; }
    
    public function setSession() {
        $_SESSION['user_id'] = $this->id;
        $_SESSION['username'] = $this->username;
        $_SESSION['nama'] = $this->nama;
        $_SESSION['role'] = $this->role;
        $_SESSION['rayon'] = $this->rayon ?? '';
        $_SESSION['rombel'] = $this->rombel ?? '';
        $_SESSION['nis'] = $this->nis ?? '';
    }
}
?>