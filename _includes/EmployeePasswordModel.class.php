<?php


class EmployeePasswordModel extends BaseModel
{
    protected string $dbTable = "employee";
    protected string $primaryKeyName = "employee_id";

    protected array $dbKeys = ["password"];

    public string $password = "";


    public function isValid(): bool
    {

        if (!$this->password)
            return false;

        return true;
    }
}