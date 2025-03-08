<?php
    $server = 'sql307.infinityfree.com';
    $login = 'if0_38457290';
    $mdp = '9ydXrkB6sn';
    $db = 'if0_38457290_authapi';
    try {
        // Connexion au serveur MySQL
        $linkpdo = new PDO("mysql:host=$server;dbname=$db;charset=utf8mb4", $login, $mdp);
        $linkpdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        require_once 'jwt_utils.php'; // Inclusion du fichier jwt_utils.php

        header("Content-Type: application/json"); // Réponse en JSON

        $secret_key = "cyberquantiquebattlepassiciel"; // Remplace par une clé secrète sécurisée

        // Récupération de la méthode HTTP
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            // Récupération des données JSON envoyées
            $input_data = json_decode(file_get_contents("php://input"), true);

            if (isset($input_data['login']) && isset($input_data['password'])) {
                $login = $input_data['login'];
                $password = $input_data['password'];

                $stmt = $linkpdo->prepare("SELECT * FROM Utilisateur WHERE Login = :login");
                $stmt->execute([':login' => $login]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) { // Exemple fictif
                    $hashedInput = hash_hmac('sha256', $password, 'ripbozo');
                    if ($hashedInput === $user['Mdp']) {
                        // Génération du JWT
                        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
                        $payload = [
                            'login' => $login,
                            'exp' => time() + 3600 // Expire dans 1 heure
                        ];
                        $jwt = generate_jwt($headers, $payload, $secret_key);

                        echo json_encode(['token' => $jwt,'user' => $user['Login']]);
                        $_SESSION['user'] = $user['Login'];
                        header("Location: http://gestionvolley.great-site.net/index.php");
                        exit;
                    } else {
                        http_response_code(401);
                        echo json_encode(['error' => 'Identifiants invalides']);
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Identifiants invalides']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Données manquantes']);
            }
        } elseif ($method === 'GET') {
            // Vérification du token pour accéder aux ressources
            $token = get_bearer_token();

            if ($token && is_jwt_valid($token, $secret_key)) {
                echo json_encode(['message' => 'Accès autorisé', 'data' => 'Voici vos données sécurisées']);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Accès refusé']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
        }
    } catch (PDOException $e) {
        die('Erreur : '.$e->getMessage()); // Afficher une erreur explicite
    }
?>
