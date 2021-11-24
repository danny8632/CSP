<?php

declare(strict_types=1);

namespace app\models;

use app\core\Application;
use app\core\db\DbModel;
use app\core\exception\ExpiredException;
use DateTime;


class RefreshToken extends DbModel
{
    public int       $id     = 0;
    public int       $user_id = 0;
    public string    $token  = '';
    public ?DateTime $expire = null;

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
        return ['id', 'user_id', 'token', 'expire'];
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
        $expire           = (new DateTime())->setTimestamp(time() + 2678400); // Adds 31 days to expire time

        $statement = Application::$app->db->prepare("DELETE FROM RefreshToken WHERE user_id = :user_id AND expire < NOW();");
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();

        //  Generate new token
        $token = bin2hex(random_bytes(30));
        $refreshToken->token = $token;
        $refreshToken->expire = $expire;

        $statement = Application::$app->db->prepare("INSERT INTO RefreshToken (user_id, token, expire) VALUES (:user_id, :token, :expire);");
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':token', crypt($token, Application::$app->tokenSalt));
        $statement->bindValue(':expire', $expire->format('Y-m-d H:i:s'));
        $statement->execute();


        return $refreshToken;
    }

    public function validate(): bool
    {
        if (parent::validate() === false) {
            return false;
        }

        $statement = Application::$app->db->prepare("SELECT id, user_id, token, expire FROM RefreshToken WHERE token = :token AND user_id = :user_id ORDER BY expire DESC LIMIT 1;");
        $statement->bindValue(':token', crypt($this->token, Application::$app->tokenSalt));
        $statement->bindValue(':user_id', $this->user_id);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_OBJ);

        if ($result === false) {
            $this->addError('token', "Invalid token");
            return false;
        }

        $refreshToken = new RefreshToken();
        $refreshToken->id      = intval($result->id);
        $refreshToken->user_id = intval($result->user_id);
        $refreshToken->token   = $result->token;
        $refreshToken->expire  = new DateTime($result->expire);

        if ($refreshToken->expire->getTimestamp() < time()) {
            throw new ExpiredException;
        }

        return true;
    }
}
