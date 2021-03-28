<?php

require "../_includes/bootstrap.inc.php";

final class UpdateKeysPage extends BaseCRUDPage
{


    private array $rooms;
    private KeyModel $key;
    private ?int $update;

    protected function setUp(): void
    {
        parent::setUp();
        $this->state = $this->getSate();


        if(!$this->isLogin())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }
        if(!$this->isAdmin())
        {
            $this->title = "Chyba";
            $this->state = self::STATE_REJECTED;
        }

        $this->update = $_GET['update'];

        if ($this->update === 0)
            $this->rooms = $this->getHisRooms();
        elseif ($this->update === 1)
            $this->rooms = $this->getOtherRooms();
        else
            $this->rooms = [];

        if($this->state === self::STATE_PROCESSED) {
            // je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS){
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='3;url=../employees/employee.php?employeeId=". $this->sesionStorage->get('id') ."'>";
                $this->title = "Klíč upraven";
            } elseif ($this->result === self::RESULT_FAIL){
                $this->title = "Aktualizace klíče selhala";
            }
        } elseif ($this->state === self::STATE_FORM_SENT){
            // načíst data
            $this->key = $this->readPost();
            $this->sesionStorage->set('id', $this->key->employee);
            // validovat data
            if ($this->key->isValid()){
                // uložit a přesměrovat
                $token = bin2hex(random_bytes(20));
                if ($this->update === 1) {
                    if ($this->key->insert()){
                        $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS ]);
                    } else {
                        $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
                    }
                }
                elseif ($this->update === 0) {
                    if ($this->key->delete()){
                        $this->sesionStorage->set($token, ['result' => self::RESULT_SUCCESS ]);

                    } else {
                        $this->sesionStorage->set($token, ['result' => self::RESULT_FAIL ]);
                    }
                }

                $this->redirect($token);

            } else {
                // jít na formulář
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Aktualizovat klíče: Neplatný formulář";
            }
        } elseif ($this->state !== self::STATE_REJECTED) {
            // přejít na formulář
            $this->title = "Aktualizovat klíče";
            $employee_id = $this->findId();
            if(!$employee_id)
                throw new RequestException(400);
            $this->key = new KeyModel();
            $this->key->employee = $employee_id;
            if (!$this->key)
                throw new RequestException(400);
        }

    }

    protected function body(): string
    {
        if($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("keyForm", ['update' => $this->update, 'key' => $this->key, 'rooms' => $this->rooms ]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS){
                return $this->m->render("keySuccess", ["message" => "Upravení zaměstnance proběhlo úspěšně", "employeeId" => $this->sesionStorage->get('id')]);
            } elseif ($this->result === self::RESULT_FAIL){
                return $this->m->render("keyFail", ["message" => "Upravení zaměstnance selhalo", "employeeId" => $this->sesionStorage->get('id')]);
            }
        } elseif ($this->state === self::STATE_REJECTED){
            return $this->m->render("fail", ["message" => "Nejste přihlášen, nebo nemáte dostatečná práva"]);
        }

    }

    protected function getSate() : int {
        if($this->isProcessed()){
            return self::STATE_PROCESSED;
        }

        $action = filter_input( INPUT_POST, 'action');
        if ($action === 'update'){
            return self::STATE_FORM_SENT;
        } else {
            return self::STATE_FORM_REQUESTED;
        }
    }
//
    private function findId() : ?int {
        $employee_id = filter_input( INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        return $employee_id;
    }
//
    private function findKeyId(int $employee, int $room) : ?int {
        $keys = [];
        $stmt = $this->pdo->prepare("SELECT * FROM `key`");

        $stmt->execute();

        while ($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){
            if ($employee === $row['employee']){
                if ($room === $row['room']){
                    return $row['key_id'];
                }
            }
        }
        return null;
    }

    private function readPost() : KeyModel {
        $key = [];

        $key['employee'] = filter_input( INPUT_POST, 'employee');
        $key['room'] = filter_input( INPUT_POST, 'room');

        $key['key_id'] = $this->findKeyId($key['employee'],$key['room']);

        return new KeyModel($key);
    }
    private function getHisRooms() : array {
        $rooms = [];
        $stmt = $this->pdo->prepare("SELECT `key`.room, room_id, `name`, employee  FROM `key` JOIN room
                                    ON employee=? WHERE room.room_id = `key`.room");

        $stmt->execute([$this->findId()]);

        while ($row = $stmt->fetch()){
            $rooms[] = $row;
        }


        return $rooms;
    }
    private function getOtherRooms() : array {
        $rooms = [];
        $stmt = $this->pdo->prepare("SELECT name, room_id FROM room");

        $stmt->execute();


        while ($row = $stmt->fetch($this->pdo::FETCH_ASSOC)){
            $contains = false;

            $stmt2 = $this->pdo->prepare("SELECT `key`.room, room_id, `name`, employee  FROM `key` JOIN room
                                    ON employee=? WHERE room.room_id = `key`.room");

            $stmt2->execute([$this->findId()]);

            while ($row2 = $stmt2->fetch($this->pdo::FETCH_ASSOC)){
                if ($row['name'] === $row2['name']){
                    $contains = true;
                }
            }
            if(!$contains){
                $rooms[] = $row;
            }
        }

        return $rooms;
    }
}

$page = new UpdateKeysPage();
$page->render();
