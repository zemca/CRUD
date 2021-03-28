<?php

require "../_includes/bootstrap.inc.php";

final class RoomsPage extends BaseDBPage
{
    private $room;
    private $employees;
    private $keys;
    private $state = true;

    protected function setUp(): void
    {
        parent::setUp();
        if(!$this->isLogin())
        {
            $this->title = "Chyba";
            $this->state = false;
        }

        $roomId = (int) ($_GET['roomId'] ?? 0);

        $rooms = $this->pdo->prepare("SELECT *, room.name as 'rname' FROM room WHERE `room`.`room_id`=:roomId");
        $rooms->execute(['roomId' => $roomId]);
        if ($rooms->rowCount() == 0)
            throw new RequestException(404);
        $this->room = $rooms->fetch();
        $this->employees = $this->pdo->prepare('SELECT *, employee.name as "ename" FROM `employee` JOIN `room` WHERE `room`.`room_id`=`employee`.`room`  AND `room`.`room_id`=:roomId');
        $this->employees->execute(['roomId' => $roomId]);
        $this->keys = $this->pdo->prepare('SELECT *, room.name as "roomname",`key`.`room` as "roomid" FROM `key` JOIN `room`, `employee` WHERE `key`.`room`=`room`.`room_id` AND `employee`.`employee_id`=`key`.`employee` AND `room`.`room_id`=:roomId');
        $this->keys->execute(['roomId' => $roomId]);



        $this->title = "Karta místnosti č. {$this->room->no}";
    }

    protected function body(): string
    {
        if (!$this->state)
            return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
        else
            return $this->m->render("room", ["room" => $this->room, "employees" => $this->employees, "keys" => $this->keys]);
    }

}

$page = new RoomsPage();
$page->render();
