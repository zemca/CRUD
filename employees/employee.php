<?php

require "../_includes/bootstrap.inc.php";

final class EmployeePage extends BaseDBPage
{
    private $employee;
    private $keys;
    private $employee_id;
    private $state = true;

    protected function setUp(): void
    {
        parent::setUp();
        if(!$this->isLogin())
        {
            $this->title = "Chyba";
            $this->state = false;
        }

        $this->employee_id = ($_GET["employeeId"]);

        $this->employee = $this->pdo->prepare('SELECT employee.name, employee.surname, employee.job, employee.wage, 
                                room.name AS "r_name", room_id FROM employee JOIN room 
                                ON room.room_id = employee.room WHERE employee_id=?');

        $this->employee->execute([$this->employee_id]);
        $this->keys = $this->pdo->prepare("SELECT `key`.room, room_id, `name`, employee  FROM `key` JOIN room
                                    ON employee=? WHERE room.room_id = `key`.room");
        $this->keys->execute([$this->employee_id]);

        $this->title = "Zaměstnanec";

    }
    protected function body(): string
    {
        if (!$this->state)
            return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
        else
            return $this->m->render("employee", ["roomDetail" => "../rooms/room.php", "employee" => $this->employee, "keys" => $this->keys, "employee_id" => $this->employee_id]);
    }
}

$page = new EmployeePage();
$page->render();

