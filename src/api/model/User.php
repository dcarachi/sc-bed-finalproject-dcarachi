<?php
namespace com\icemalta\kahuna\api\model;

use \JsonSerializable;
use \PDO;

class User implements JsonSerializable
{
    private static PDO $db;

    private int $id;
    private ?string $firstName;
    private ?string $lastName;
    private ?string $email;
    private ?string $password;
    private ?AccessLevel $accessLevel;

    public function __construct(?string $email = null, ?string $password = null, ?AccessLevel $accessLevel = null, ?string $firstName = null, ?string $lastName = null, int $id = 0)
    {
        $this->email = $email;
        $this->password = $password;
        $this->accessLevel = $accessLevel;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->id = $id;
        self::$db = DBConnect::getInstance()->getConnection();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'accessLevel' => $this->accessLevel
        ];
    }

    /**
     * Performs an "upsert" on the `User` table.
     * @param \com\icemalta\kahuna\api\model\User $user The user object to persist to the database.
     * @return User|null Returns the User with updated fields if successful, or `null` on failure.
     */
    public static function save(User $user): ?User
    {
        $hashed = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        if ($user->getId() === 0) {
            // Insert new user
            $sql = 'INSERT INTO User(email, password, firstName, lastName, accessLevel) VALUES
            (:email, :password, :firstName, :lastName, :accessLevel)';
            $sth = self::$db->prepare($sql);
        } else {
            // Update existing user
            $sql = 'UPDATE User SET email = :email, password = :password,
            firstName = :firstName, lastName = :lastName, accessLevel = :accessLevel WHERE id = :id';
            $sth = self::$db->prepare($sql);
            $sth->bindValue('id', $user->getId());
        }
        $sth->bindValue('email', $user->getEmail());
        $sth->bindValue('password', $hashed);
        $sth->bindValue('accessLevel', $user->getAccessLevel()->value);
        $sth->bindValue('firstName', $user->getFirstName());
        $sth->bindValue('lastName', $user->getLastName());
        $sth->execute();

        if ($sth->rowCount() > 0) {
            if ($user->getId() === 0) {
                $user->setId(self::$db->lastInsertId());
            }
            return $user;
        }
        return null;
    }

    /**
     * Retrieves a User with a given user id.
     * @param \com\icemalta\kahuna\api\model\User $user A User object with the id field to search for.
     * @return User|null Returns the User with populated fields if successful, or `null` on failure.
     */
    public static function get(User $user): ?User
    {
        $sql = 'SELECT * FROM User WHERE id = :id';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('id', $user->getId());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result) {
            return new User(
                id: $result->id,
                firstName: $result->firstName,
                lastName: $result->lastName,
                email: $result->email,
                password: $result->password,
                accessLevel: AccessLevel::from($result->accessLevel)
            );
        }
        return null;
    }

    /**
     * Authenticates a user.
     * @param string $email The user's email credential.
     * @param string $password The user's password credential.
     * @return User|null Returns the User with populated fields if successful, or `null` on failure.
     */
    public static function authenticate(string $email, string $password): ?User
    {
        self::$db = DBConnect::getInstance()->getConnection();

        $sql = 'SELECT * FROM User WHERE email = :email';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('email', $email);
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result && password_verify($password, $result->password)) {
            return new User(
                id: $result->id,
                firstName: $result->firstName,
                lastName: $result->lastName,
                email: $result->email,
                password: $result->password,
                accessLevel: AccessLevel::from($result->accessLevel)
            );
        }
        return null;
    }


    /**
     * Checks whether a User email address can be used for registration.
     * @param \com\icemalta\kahuna\api\model\User $user The user object with the email field to search for.
     * @return bool Returns `true` if the email address is available for user, or `false` otherwise.
     */
    public static function isEmailAvailable(User $user): bool
    {
        $sql = 'SELECT COUNT(*) AS userCount FROM User WHERE email = :email';
        $sth = self::$db->prepare($sql);
        $sth->bindValue('email', $user->getEmail());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        return $result->userCount === 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getAccessLevel(): ?AccessLevel
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(AccessLevel $accessLevel): self
    {
        $this->accessLevel = $accessLevel;
        return $this;
    }
}