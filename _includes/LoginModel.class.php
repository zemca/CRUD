<?php


class LoginModel extends BaseModel
{
    protected string $primaryKeyName = "";

    protected array $dbKeys = ["username", "password"];

    public string $username = "";
    public string $password = "";

    public function isValid(): bool
    {

        if (!$this->username)
            return false;
        if (!$this->password)
            return false;

        return true;
    }
}