<?php

require "../_includes/bootstrap.inc.php";

final class ListEmployeePage extends BaseDBPage
{
    private $state = true;

    protected function setUp(): void
    {
        parent::setUp();
        if(!$this->isLogin())
        {
            $this->title = "Chyba";
            $this->state = false;
        }
        $this->title = "Seznam zaměstnanců";
    }

    protected function body(): string
    {
        if (!$this->state)
            return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
        else {
            $stmt = $this->pdo->prepare("SELECT employee.name as employeename, employee.surname, employee.employee_id, employee.job, employee.room, room.name, room.phone FROM employee JOIN room WHERE employee.room = room.room_id");
            $stmt->execute([]);
            return $this->m->render("employeeList", ["employeeDetail" => "employee.php", "employees" => $stmt]);
        }
    }

}

$page = new ListEmployeePage();
$page->render();
