<?php


final class EmployeeModel extends BaseModel
{
    protected string $dbTable = "employee";
    protected string $primaryKeyName = "employee_id";

    protected array $dbKeys = ["name", "surname", "job", "wage", "room", "username", "password", "admin"];

    public string $name = "";
    public string $surname = "";
    public string $job = "";
    public string $wage = "";
    public int $room = 0;
    public string $username = "";
    public string $password = "";
    public bool $admin = false;

    public function isValid(): bool
    {

        if (!$this->name)
            return false;
        if (!$this->surname)
            return false;
        if (!$this->job)
            return false;
        if (!$this->wage)
            return false;
        if ($this->room <= 0)
            return false;
        if (!$this->username)
            return false;
        if (!$this->password)
            return false;


        return true;
    }
}