<?php


class KeyModel extends BaseModel
{
    protected string $dbTable = "key";
    protected string $primaryKeyName = "key_id";

    protected array $dbKeys = ["employee", "room"];

    public string $employee = "";
    public int $room = 0;

    public function isValid() : bool {
        if (!$this->employee)
            return false;
        if (!$this->room)
            return false;

        return true;
    }
}