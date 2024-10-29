<?php
namespace com\icemalta\kahuna\api\model;

use \JsonSerializable;
use \PDO;

class User implements JsonSerializable
{
    private int $id;
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private AccessLevel $accessLevel;

    private static PDO $dbh;

    public function __construct(string $email, string $password, ?AccessLevel $accessLevel, ?string $firstName, ?string $lastName, ?int $id = 0)
    {
        $this->email = $email;
        $this->password = $password;
        $this->accessLevel = $accessLevel;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->id = $id;
        self::$dbh = DBConnect::getInstance()->getConnection();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'accessLevel' => $this->accessLevel
        ];
    }

    /**
     * Performs an "upsert" on the User table. No checks are made to determine the uniqueness of the email address.
     * Callers should call `User::isEmailAvailable` to perform this check prior to calling this function.
     * @param \com\icemalta\kahuna\api\model\User $user The user data to insert or update on the database.
     * @return User|null Returns the new or updated User if successful, or null on failure.
     */
    public static function save(User $user): ?User
    {
        $hashed = password_hash($user->getPassword(), PASSWORD_DEFAULT);
        if ($user->getId() === 0) {
            // Insert new user
            $sql = 'INSERT INTO User(email, password, firstName, lastName, accessLevel) VALUES
            (:email, :password, :firstName, :lastName, :accessLevel)';
            $sth = self::$dbh->prepare($sql);
            $sth->bindValue('email', $user->getEmail());
            $sth->bindValue('accessLevel', $user->getAccessLevel()->value);
        } else {
            // Update existing user
            $sql = 'UPDATE User SET password = :password, firstName = :firstName, lastName = :lastName WHERE id = :id';
            $sth = self::$dbh->prepare($sql);
            $sth->bindValue('id', $user->getId());
        }
        $sth->bindValue('password', $hashed);
        $sth->bindValue('firstName', $user->getFirstName());
        $sth->bindValue('lastName', $user->getLastName());
        $sth->execute();

        if ($sth->rowCount() > 0) {
            if ($user->getId() === 0) {
                $user->setId(self::$dbh->lastInsertId());
            }
            return $user;
        }
        return null;
    }

    /**
     * Authenticates a user.
     * @param \com\icemalta\kahuna\api\model\User $user The user with email address and password as credentials
     * to authenticate.
     * @return User|null Returns the fully populated `User` if successful, otherwise it returns `null`.
     */
    public static function authenticate(User $user): ?User
    {
        $sql = 'SELECT * FROM User WHERE email = :email';
        $sth = self::$dbh->prepare($sql);
        $sth->bindValue('email', $user->getEmail());
        $sth->execute();

        $result = $sth->fetch(PDO::FETCH_OBJ);
        if ($result && password_verify($user->getPassword(), $result->password)) {
            return new User(
                email: $result->email,
                password: $result->password,
                accessLevel: $result->accessLevel,
                firstName: $result->firstName,
                lastName: $result->lastName,
                id: $result->id
            );
        }
        return null;
    }

    /**
     * Checks if a given user email address is available for registration.
     * @param string $email The email address to check for.
     * @return bool Returns `true` if the email address is available, `false` otherwise.
     */
    public static function isEmailAvailable(string $email): bool
    {
        self::$dbh = DBConnect::getInstance()->getConnection();

        $sql = 'SELECT COUNT(*) AS userCount FROM User WHERE User.email = :email';
        $sth = self::$dbh->prepare($sql);
        $sth->bindValue('email', $email);
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getAccessLevel(): AccessLevel
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(AccessLevel $accessLevel): self
    {
        $this->accessLevel = $accessLevel;
        return $this;
    }
}