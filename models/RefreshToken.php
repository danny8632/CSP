<?php

declare(strict_types=1);

namespace app\models;

use app\core\Application;
use app\core\db\DbModel;
use app\core\exception\ExpiredException;


class RefreshToken extends DbModel
{
    public int    $id      = 0;
    public int    $user_id = 0;
    public string $token   = '';
    public ?int   $expire  = null;

    public function tableName(): string
    {
        return 'RefreshToken';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function attributes(): array
    {
        return ['user_id', 'token', 'expire'];
    }

    public function properties(): array
    {
        return ['id', 'user_id', 'token', 'expire'];
    }

    public function rules(): array
    {
        return [
            'user_id' => [self::RULE_REQUIRED, self::RULE_INT],
            'token'  => [self::RULE_REQUIRED],
        ];
    }

    public static function new(int $user_id): RefreshToken
    {
        $refreshToken     = new RefreshToken();
        $refreshToken->id = $user_id;
        $expire           = time() + 2678400; // Adds 31 days to expire time

        $statement = Application::$app->db->prepare("DELETE FROM RefreshToken WHERE user_id = :user_id AND expire < NOW();");
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();

        //  Generate new token
        $token = bin2hex(random_bytes(30));
        $refreshToken->token = $token;
        $refreshToken->expire = $expire;

        $statement = Application::$app->db->prepare("INSERT INTO RefreshToken (user_id, token, expire) VALUES (:user_id, :token, FROM_UNIXTIME(:expire));");
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':token', crypt($token, Application::$app->tokenSalt));
        $statement->bindValue(':expire', $expire);
        $statement->execute();


        return $refreshToken;
    }

    public function validate(): bool
    {
        if (parent::validate() === false) {
            return false;
        }

        $statement = Application::$app->db->prepare("SELECT id, user_id, token, UNIX_TIMESTAMP(expire) FROM RefreshToken WHERE token = :token AND user_id = :user_id ORDER BY expire DESC LIMIT 1;");
        $statement->bindValue(':token', crypt($this->token, Application::$app->tokenSalt));
        $statement->bindValue(':user_id', $this->user_id);
        $statement->execute();

        $refreshToken = $statement->fetchObject(RefreshToken::class);

        if ($refreshToken === false) {
            $this->addError('token', "Invalid token");
            return false;
        }

        if ($refreshToken->expire < time()) {
            throw new ExpiredException;
        }

        return true;
    }
}
